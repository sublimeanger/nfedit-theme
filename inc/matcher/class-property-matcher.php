<?php
/**
 * Property matcher: pairs cottages.com Awin feed rows with their Snaptrip
 * equivalents, computes confidence scores, and populates the matches table.
 *
 * Confidence rubric (highest applicable wins):
 *   99 — distance <= 30m AND postcode exact AND sleeps exact
 *   97 — distance <= 50m AND postcode exact AND sleeps within +/-1
 *   92 — distance <= 100m AND postcode exact AND sleeps within +/-1
 *   85 — distance <= 200m AND postcode outward match AND sleeps within +/-1
 *   78 — distance <= 500m AND postcode outward match AND sleeps within +/-1
 *   60 — distance <= 50m only (no postcode or sleeps confirm)
 *    0 — no candidate within 500m
 *
 * Auto-route:
 *   confidence >= 95 -> 'snaptrip'
 *   confidence 75-94 -> 'review_needed'
 *   confidence 1-74  -> 'cottages_com'
 *   confidence 0     -> 'no_match'
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Property_Matcher {

    const TABLE_MATCHES      = 'wp_nfedit_property_matches';
    const TABLE_COTTAGES_COM = 'wp_nfedit_cottages_com_inventory';
    const TABLE_SNAPTRIP     = 'wp_nfedit_snaptrip_listings';

    const SEARCH_RADIUS_M    = 500;
    const AUTO_ROUTE_MIN     = 95.0;
    const REVIEW_MIN         = 75.0;

    private $log_file;
    private $stats = array(
        'cottages_com_rows_total' => 0,
        'matched_auto_snaptrip'   => 0,
        'matched_review_needed'   => 0,
        'matched_cottages_com'    => 0,
        'no_match'                => 0,
        'many_to_one_warnings'    => 0,
        'disambig_winners_kept'   => 0,
        'disambig_runners_demoted'=> 0,
        'disambig_all_demoted_ambig' => 0,
        'disambig_winners_promoted_via_name' => 0,
        'review_applied_snaptrip'    => 0,
        'review_applied_cottages_com'=> 0,
        'review_applied_no_match'    => 0,
    );

    public function __construct() {
        $upload = wp_upload_dir();
        $this->log_file = trailingslashit( $upload['basedir'] ) . 'property-matcher.log';
    }

    public function log( $msg ) {
        $line = '[' . gmdate( 'Y-m-d H:i:s' ) . 'Z] ' . $msg . "\n";
        file_put_contents( $this->log_file, $line, FILE_APPEND );
        echo $line;
    }

    public function get_stats() {
        return $this->stats;
    }

    public function haversine_meters( $lat1, $lng1, $lat2, $lng2 ) {
        $R = 6371000.0;
        $phi1 = deg2rad( (float) $lat1 );
        $phi2 = deg2rad( (float) $lat2 );
        $dphi = deg2rad( (float) $lat2 - (float) $lat1 );
        $dlmb = deg2rad( (float) $lng2 - (float) $lng1 );
        $a = sin( $dphi / 2 ) ** 2
           + cos( $phi1 ) * cos( $phi2 ) * sin( $dlmb / 2 ) ** 2;
        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
        return $R * $c;
    }

    public function score_pair( $cottage, $snaptrip ) {
        if ( $cottage->latitude === null || $cottage->longitude === null
          || $snaptrip->latitude === null || $snaptrip->longitude === null ) {
            return null;
        }

        $distance = $this->haversine_meters(
            $cottage->latitude, $cottage->longitude,
            $snaptrip->latitude, $snaptrip->longitude
        );

        if ( $distance > self::SEARCH_RADIUS_M ) {
            return null;
        }

        $cpc = strtoupper( str_replace( ' ', '', (string) $cottage->postcode ) );
        $spc = strtoupper( str_replace( ' ', '', (string) $snaptrip->postcode ) );
        $postcode_exact = ( $cpc !== '' && $cpc === $spc ) ? 1 : 0;

        $postcode_outward_match = 0;
        if ( strlen( $cpc ) >= 5 && strlen( $spc ) >= 5 ) {
            if ( substr( $cpc, 0, -3 ) === substr( $spc, 0, -3 ) ) {
                $postcode_outward_match = 1;
            }
        }

        $csleeps = $cottage->sleeps !== null ? (int) $cottage->sleeps : null;
        $ssleeps = $snaptrip->sleeps !== null ? (int) $snaptrip->sleeps : null;
        $sleeps_exact    = ( $csleeps !== null && $ssleeps !== null && $csleeps === $ssleeps ) ? 1 : 0;
        $sleeps_within_1 = ( $csleeps !== null && $ssleeps !== null && abs( $csleeps - $ssleeps ) <= 1 ) ? 1 : 0;

        $confidence = 0.0;
        $method     = 'no_match';

        if ( $distance <= 30.0 && $postcode_exact && $sleeps_exact ) {
            $confidence = 99.0;
            $method     = 'lt_30m_pc_exact_sleeps_exact';
        } elseif ( $distance <= 50.0 && $postcode_exact && $sleeps_within_1 ) {
            $confidence = 97.0;
            $method     = 'lt_50m_pc_exact_sleeps_within_1';
        } elseif ( $distance <= 100.0 && $postcode_exact && $sleeps_within_1 ) {
            $confidence = 92.0;
            $method     = 'lt_100m_pc_exact_sleeps_within_1';
        } elseif ( $distance <= 200.0 && $postcode_outward_match && $sleeps_within_1 ) {
            $confidence = 85.0;
            $method     = 'lt_200m_outward_sleeps_within_1';
        } elseif ( $distance <= 500.0 && $postcode_outward_match && $sleeps_within_1 ) {
            $confidence = 78.0;
            $method     = 'lt_500m_outward_sleeps_within_1';
        } elseif ( $distance <= 50.0 ) {
            $confidence = 60.0;
            $method     = 'lt_50m_only_no_confirm';
        } else {
            return null;
        }

        return array(
            'snaptrip'              => $snaptrip,
            'distance'              => $distance,
            'postcode_exact'        => $postcode_exact,
            'postcode_outward_match'=> $postcode_outward_match,
            'sleeps_exact'          => $sleeps_exact,
            'sleeps_within_1'       => $sleeps_within_1,
            'confidence'            => $confidence,
            'method'                => $method,
        );
    }

    public function find_best_match( $cottage, $snaptrip_rows ) {
        $candidates = array();
        foreach ( $snaptrip_rows as $s ) {
            $score = $this->score_pair( $cottage, $s );
            if ( $score !== null ) {
                $candidates[] = $score;
            }
        }
        if ( empty( $candidates ) ) {
            return null;
        }

        usort( $candidates, function( $a, $b ) {
            if ( $a['confidence'] !== $b['confidence'] ) {
                return $b['confidence'] <=> $a['confidence'];
            }
            if ( $a['distance'] !== $b['distance'] ) {
                return $a['distance'] <=> $b['distance'];
            }
            return $b['sleeps_exact'] <=> $a['sleeps_exact'];
        } );

        return array(
            'best'                  => $candidates[0],
            'candidates_considered' => count( $candidates ),
        );
    }

    public function resolve_route( $confidence ) {
        if ( $confidence >= self::AUTO_ROUTE_MIN ) { return 'snaptrip'; }
        if ( $confidence >= self::REVIEW_MIN )     { return 'review_needed'; }
        if ( $confidence > 0 )                      { return 'cottages_com'; }
        return 'no_match';
    }

    public function run() {
        global $wpdb;
        $this->log( '=== MATCHER RUN START ===' );

        $snaptrip_rows = $wpdb->get_results(
            "SELECT id, snaptrip_ref, title, latitude, longitude, postcode, sleeps, bedrooms, price_per_night_from, review_count, detail_url, image_url
             FROM " . self::TABLE_SNAPTRIP . "
             WHERE latitude IS NOT NULL AND longitude IS NOT NULL"
        );
        $this->log( 'Loaded ' . count( $snaptrip_rows ) . ' Snaptrip candidates' );

        $cottage_rows = $wpdb->get_results(
            "SELECT id, merchant_product_id, title, latitude, longitude, postcode, sleeps, review_avg, weekly_price_from
             FROM " . self::TABLE_COTTAGES_COM . "
             WHERE latitude IS NOT NULL AND longitude IS NOT NULL"
        );
        $this->stats['cottages_com_rows_total'] = count( $cottage_rows );
        $this->log( 'Matching against ' . count( $cottage_rows ) . ' cottages.com NF rows' );

        $wpdb->query( "TRUNCATE TABLE " . self::TABLE_MATCHES );

        foreach ( $cottage_rows as $cottage ) {
            $result = $this->find_best_match( $cottage, $snaptrip_rows );

            if ( $result === null ) {
                $wpdb->insert( self::TABLE_MATCHES, array(
                    'cottages_com_inventory_id' => $cottage->id,
                    'snaptrip_listing_id'       => null,
                    'confidence'                => 0.00,
                    'match_method'              => 'no_candidate_within_500m',
                    'auto_route'                => 'no_match',
                    'candidates_considered'     => 0,
                    'created_at'                => current_time( 'mysql', 1 ),
                ) );
                $this->stats['no_match']++;
                continue;
            }

            $best = $result['best'];
            $route = $this->resolve_route( $best['confidence'] );

            $wpdb->insert( self::TABLE_MATCHES, array(
                'cottages_com_inventory_id' => $cottage->id,
                'snaptrip_listing_id'       => $best['snaptrip']->id,
                'confidence'                => $best['confidence'],
                'match_method'              => $best['method'],
                'auto_route'                => $route,
                'distance_meters'           => $best['distance'],
                'postcode_exact'            => $best['postcode_exact'],
                'postcode_outward_match'    => $best['postcode_outward_match'],
                'sleeps_exact'              => $best['sleeps_exact'],
                'sleeps_within_1'           => $best['sleeps_within_1'],
                'candidates_considered'     => $result['candidates_considered'],
                'created_at'                => current_time( 'mysql', 1 ),
            ) );

            if ( $route === 'snaptrip' )       { $this->stats['matched_auto_snaptrip']++; }
            elseif ( $route === 'review_needed' ) { $this->stats['matched_review_needed']++; }
            elseif ( $route === 'cottages_com' )  { $this->stats['matched_cottages_com']++; }
            else                                  { $this->stats['no_match']++; }
        }

        $dupes = $wpdb->get_results(
            "SELECT snaptrip_listing_id, COUNT(*) AS n
             FROM " . self::TABLE_MATCHES . "
             WHERE snaptrip_listing_id IS NOT NULL
             GROUP BY snaptrip_listing_id
             HAVING COUNT(*) > 1"
        );
        $this->stats['many_to_one_warnings'] = count( $dupes );
        foreach ( $dupes as $d ) {
            $this->log( "Many-to-one warning: Snaptrip listing #{$d->snaptrip_listing_id} matched by {$d->n} cottages.com rows" );
        }

        $this->log( '=== MATCHER RUN DONE ===' );
    }

    /* ------------------------------------------------------------------
     * Name-similarity helpers (used during disambiguation)
     * ------------------------------------------------------------------ */

    /**
     * Extract significant tokens from a property title.
     * Lowercase, split on non-alphanumeric, length >= 4, not in stop list.
     */
    private function extract_significant_tokens( $title ) {
        static $stop_words = array(
            // Property type words
            'cottage','cottages','house','houses','lodge','lodges','cabin','cabins',
            'barn','barns','retreat','retreats','hut','huts','suite','suites',
            'apartment','apartments','annexe','annex','annexes','room','rooms',
            'studio','studios','farm','farmhouse',
            // Articles, prepositions
            'the','and','near','with','for','from','through',
            // Property descriptors
            'holiday','holidays','self','catering','pet','dog','friendly','bed',
            'beds','bedroom','bedrooms',
            // NF site context (everything is New Forest here)
            'forest','new',
            // NF town names (so anonymized titles like "1 Bed Cottage in Lymington"
            // yield zero distinctive tokens)
            'lymington','brockenhurst','lyndhurst','beaulieu','ringwood','burley',
            'fordingbridge','christchurch','bransgore','milton','milford','hordle',
            'boldre','sway','hythe','totton','cadnam','verwood','romsey','salisbury',
            'hampshire','dorset','england',
        );

        $title = strtolower( (string) $title );
        $parts = preg_split( '/[^a-z0-9]+/', $title );
        $out = array();
        foreach ( $parts as $t ) {
            if ( $t === '' ) continue;
            if ( strlen( $t ) < 4 ) continue;
            if ( in_array( $t, $stop_words, true ) ) continue;
            $out[] = $t;
        }
        return array_values( array_unique( $out ) );
    }

    /**
     * Compute name match between two titles.
     * Returns 1 if any distinctive token appears in both, else 0.
     */
    public function compute_name_match( $title_a, $title_b ) {
        $a = $this->extract_significant_tokens( $title_a );
        $b = $this->extract_significant_tokens( $title_b );
        if ( empty( $a ) || empty( $b ) ) {
            return 0;
        }
        return count( array_intersect( $a, $b ) ) > 0 ? 1 : 0;
    }

    /* ------------------------------------------------------------------
     * Resolve many-to-one Snaptrip claims (disambiguation pass).
     *
     * For each Snaptrip ID claimed by 2+ cottages.com rows:
     *   - If top claim's confidence >= AUTO_ROUTE_MIN: keep top, demote rest
     *   - Else: demote ALL (cluster ambiguity with no clear winner)
     *
     * Demoted rows: snaptrip_listing_id = NULL, confidence = 0,
     * auto_route = 'cottages_com', notes records the original claim.
     * ------------------------------------------------------------------ */

    public function resolve_many_to_one() {
        global $wpdb;
        $this->log( '=== DISAMBIGUATION PASS START (name-aware) ===' );

        $dupes = $wpdb->get_col(
            "SELECT snaptrip_listing_id
             FROM " . self::TABLE_MATCHES . "
             WHERE snaptrip_listing_id IS NOT NULL
             GROUP BY snaptrip_listing_id
             HAVING COUNT(*) > 1"
        );
        $this->log( 'Many-to-one Snaptrip IDs to resolve: ' . count( $dupes ) );

        foreach ( $dupes as $snaptrip_id ) {
            $snaptrip = $wpdb->get_row( $wpdb->prepare(
                "SELECT title FROM " . self::TABLE_SNAPTRIP . " WHERE id = %d",
                $snaptrip_id
            ) );
            $snaptrip_title = $snaptrip ? $snaptrip->title : '';

            $claims = $wpdb->get_results( $wpdb->prepare(
                "SELECT m.id, m.cottages_com_inventory_id, m.confidence, m.distance_meters,
                        m.match_method, m.snaptrip_listing_id,
                        c.title AS cottage_title
                 FROM " . self::TABLE_MATCHES . " m
                 JOIN " . self::TABLE_COTTAGES_COM . " c ON c.id = m.cottages_com_inventory_id
                 WHERE m.snaptrip_listing_id = %d",
                $snaptrip_id
            ) );

            if ( count( $claims ) < 2 ) {
                continue;
            }

            // Compute name_match for each claim against the Snaptrip title
            foreach ( $claims as $claim ) {
                $claim->name_match = $this->compute_name_match( $claim->cottage_title, $snaptrip_title );
            }

            // Sort: name_match DESC, confidence DESC, distance ASC, id ASC
            usort( $claims, function( $a, $b ) {
                if ( $a->name_match !== $b->name_match ) {
                    return $b->name_match <=> $a->name_match;
                }
                if ( (float) $a->confidence !== (float) $b->confidence ) {
                    return ( (float) $b->confidence ) <=> ( (float) $a->confidence );
                }
                if ( (float) $a->distance_meters !== (float) $b->distance_meters ) {
                    return ( (float) $a->distance_meters ) <=> ( (float) $b->distance_meters );
                }
                return $a->id <=> $b->id;
            } );

            $winner = $claims[0];

            // Keep winner if (conf >= AUTO_ROUTE_MIN) OR (name_match == 1)
            // Strong name signal overrides the conf-only floor.
            $keep_winner = ( (float) $winner->confidence >= self::AUTO_ROUTE_MIN )
                        || ( (int) $winner->name_match === 1 );

            if ( $keep_winner ) {
                $this->log( sprintf(
                    'Snaptrip #%d ("%s"): winner is "%s" (cc #%d, conf=%s, dist=%sm, name_match=%d) — demoting %d runners-up',
                    $snaptrip_id, $snaptrip_title, $winner->cottage_title,
                    $winner->cottages_com_inventory_id, $winner->confidence,
                    $winner->distance_meters, $winner->name_match,
                    count( $claims ) - 1
                ) );
                $this->stats['disambig_winners_kept']++;
                $losers = array_slice( $claims, 1 );
                foreach ( $losers as $loser ) {
                    $this->demote_match(
                        $loser,
                        'demoted_runner_up_to_match_' . $winner->id,
                        sprintf(
                            'Originally matched to snaptrip_id=%d ("%s") at conf=%s, dist=%sm, name_match=%d; demoted because match #%d (cc #%d "%s") at conf=%s, name_match=%d won the same Snaptrip target.',
                            $loser->snaptrip_listing_id, $snaptrip_title,
                            $loser->confidence, $loser->distance_meters, $loser->name_match,
                            $winner->id, $winner->cottages_com_inventory_id,
                            $winner->cottage_title, $winner->confidence, $winner->name_match
                        )
                    );
                    $this->stats['disambig_runners_demoted']++;
                }

                // Promote winner's auto_route to 'snaptrip' when it was kept via
                // name_match but its score-derived confidence is below the
                // AUTO_ROUTE_MIN floor. Otherwise the disambig pass would
                // declare a winner whose actual routing decision still pointed
                // at cottages.com.
                if ( (float) $winner->confidence < self::AUTO_ROUTE_MIN ) {
                    $wpdb->update(
                        self::TABLE_MATCHES,
                        array(
                            'auto_route' => 'snaptrip',
                            'notes'      => sprintf(
                                'Promoted via disambig name_match signal. Original conf=%s (below AUTO_ROUTE_MIN=%s), name_match=1 on snaptrip_title="%s". Outcompeted %d runner(s).',
                                $winner->confidence, self::AUTO_ROUTE_MIN, $snaptrip_title, count( $claims ) - 1
                            ),
                        ),
                        array( 'id' => (int) $winner->id )
                    );
                    $this->stats['disambig_winners_promoted_via_name']++;
                    $this->log( sprintf(
                        'Snaptrip #%d: winner "%s" auto_route promoted to snaptrip via name_match (original conf=%s below AUTO_ROUTE_MIN=%s)',
                        $snaptrip_id, $winner->cottage_title,
                        $winner->confidence, self::AUTO_ROUTE_MIN
                    ) );
                }
            } else {
                $this->log( sprintf(
                    'Snaptrip #%d ("%s"): cluster ambiguity (top conf=%s, name_match=%d — neither threshold met) — demoting all %d claimants',
                    $snaptrip_id, $snaptrip_title, $winner->confidence,
                    $winner->name_match, count( $claims )
                ) );
                foreach ( $claims as $claim ) {
                    $this->demote_match(
                        $claim,
                        'demoted_cluster_ambiguity',
                        sprintf(
                            'Originally matched to snaptrip_id=%d ("%s") at conf=%s, dist=%sm, name_match=%d; demoted because cluster had no claim hitting conf>=%s and no claim with name_match=1.',
                            $claim->snaptrip_listing_id, $snaptrip_title,
                            $claim->confidence, $claim->distance_meters, $claim->name_match,
                            self::AUTO_ROUTE_MIN
                        )
                    );
                    $this->stats['disambig_all_demoted_ambig']++;
                }
            }
        }
        $this->log( '=== DISAMBIGUATION PASS DONE ===' );
    }

    private function demote_match( $match_row, $new_method, $notes ) {
        global $wpdb;
        $wpdb->update(
            self::TABLE_MATCHES,
            array(
                'snaptrip_listing_id' => null,
                'confidence'          => 0,
                'match_method'        => $new_method,
                'auto_route'          => 'cottages_com',
                'notes'               => $notes,
            ),
            array( 'id' => (int) $match_row->id )
        );
    }

    /* ------------------------------------------------------------------
     * Apply manual review decisions from a filled-in CSV
     *
     * CSV must have columns: match_id, decision
     * decision values: S (approve Snaptrip), C (route via cottages.com),
     *                  N (no match), blank (skip)
     * ------------------------------------------------------------------ */

    public function apply_review_decisions( $csv_path ) {
        global $wpdb;
        $this->log( '=== APPLY REVIEW DECISIONS START: ' . $csv_path . ' ===' );

        if ( ! file_exists( $csv_path ) ) {
            throw new RuntimeException( 'CSV file not found: ' . $csv_path );
        }

        $fh = fopen( $csv_path, 'r' );
        $header = fgetcsv( $fh );
        if ( ! $header ) {
            fclose( $fh );
            throw new RuntimeException( 'CSV header missing' );
        }
        $col = array_flip( $header );

        $required = array( 'match_id', 'decision' );
        foreach ( $required as $rc ) {
            if ( ! isset( $col[ $rc ] ) ) {
                fclose( $fh );
                throw new RuntimeException( "Required CSV column missing: {$rc}" );
            }
        }

        $processed = 0;
        $skipped   = 0;
        while ( ( $row = fgetcsv( $fh ) ) !== false ) {
            $match_id = isset( $row[ $col['match_id'] ] ) ? (int) $row[ $col['match_id'] ] : 0;
            $decision = isset( $row[ $col['decision'] ] ) ? strtoupper( trim( $row[ $col['decision'] ] ) ) : '';

            if ( $match_id <= 0 ) { $skipped++; continue; }
            if ( $decision === '' ) { $skipped++; continue; }
            if ( ! in_array( $decision, array( 'S', 'C', 'N' ), true ) ) {
                $this->log( "Match #{$match_id}: invalid decision '{$decision}', skipping" );
                $skipped++;
                continue;
            }

            $existing = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, snaptrip_listing_id, auto_route FROM " . self::TABLE_MATCHES . " WHERE id = %d",
                $match_id
            ) );
            if ( ! $existing ) {
                $this->log( "Match #{$match_id}: not found, skipping" );
                $skipped++;
                continue;
            }

            $now = current_time( 'mysql', 1 );

            switch ( $decision ) {
                case 'S':
                    $wpdb->update( self::TABLE_MATCHES, array(
                        'manual_decision' => 'approve_snaptrip',
                        'auto_route'      => 'snaptrip',
                        'decided_at'      => $now,
                        'decided_by'      => 'jamie',
                    ), array( 'id' => $match_id ) );
                    $this->stats['review_applied_snaptrip']++;
                    $this->log( "Match #{$match_id}: approved Snaptrip" );
                    break;
                case 'C':
                    $wpdb->update( self::TABLE_MATCHES, array(
                        'manual_decision' => 'approve_cottages_com',
                        'auto_route'      => 'cottages_com',
                        'decided_at'      => $now,
                        'decided_by'      => 'jamie',
                    ), array( 'id' => $match_id ) );
                    $this->stats['review_applied_cottages_com']++;
                    $this->log( "Match #{$match_id}: routed cottages.com (rejected Snaptrip pair)" );
                    break;
                case 'N':
                    $wpdb->update( self::TABLE_MATCHES, array(
                        'manual_decision'     => 'no_match',
                        'auto_route'          => 'no_match',
                        'snaptrip_listing_id' => null,
                        'decided_at'          => $now,
                        'decided_by'          => 'jamie',
                    ), array( 'id' => $match_id ) );
                    $this->stats['review_applied_no_match']++;
                    $this->log( "Match #{$match_id}: marked no_match" );
                    break;
            }
            $processed++;
        }
        fclose( $fh );

        $this->log( sprintf( '=== APPLY REVIEW DONE: %d processed, %d skipped ===', $processed, $skipped ) );
        return array( 'processed' => $processed, 'skipped' => $skipped );
    }

    public function export_review_csv( $path ) {
        global $wpdb;

        $rows = $wpdb->get_results(
            "SELECT
                m.id AS match_id, m.confidence, m.match_method, m.distance_meters,
                m.postcode_exact, m.sleeps_exact, m.candidates_considered,
                c.id AS cottages_com_id, c.merchant_product_id, c.title AS cottages_com_title,
                c.postcode AS cottages_com_postcode, c.town AS cottages_com_town,
                c.sleeps AS cottages_com_sleeps, c.review_avg AS cottages_com_review,
                c.weekly_price_from AS cottages_com_weekly_price, c.image_url AS cottages_com_image,
                c.merchant_deep_link,
                s.id AS snaptrip_id, s.snaptrip_ref, s.title AS snaptrip_title,
                s.postcode AS snaptrip_postcode, s.sleeps AS snaptrip_sleeps,
                s.bedrooms AS snaptrip_bedrooms, s.review_count AS snaptrip_reviews,
                s.price_per_night_from AS snaptrip_nightly_from, s.detail_url AS snaptrip_url,
                s.image_url AS snaptrip_image
            FROM " . self::TABLE_MATCHES . " m
            JOIN " . self::TABLE_COTTAGES_COM . " c ON c.id = m.cottages_com_inventory_id
            LEFT JOIN " . self::TABLE_SNAPTRIP . " s ON s.id = m.snaptrip_listing_id
            WHERE m.auto_route = 'review_needed'
            ORDER BY m.confidence DESC, c.review_avg DESC"
        );

        $fh = fopen( $path, 'w' );
        if ( ! $fh ) {
            throw new RuntimeException( 'Failed to open CSV output: ' . $path );
        }

        fputcsv( $fh, array(
            'match_id', 'decision',
            'confidence', 'distance_m', 'method', 'postcode_exact', 'sleeps_exact', 'candidates',
            'cottages_com_id', 'cottages_com_mpid', 'cottages_com_title',
            'cottages_com_postcode', 'cottages_com_town', 'cottages_com_sleeps',
            'cottages_com_review_avg', 'cottages_com_weekly_£', 'cottages_com_image',
            'cottages_com_deep_link',
            'snaptrip_id', 'snaptrip_ref', 'snaptrip_title',
            'snaptrip_postcode', 'snaptrip_sleeps', 'snaptrip_bedrooms',
            'snaptrip_review_count', 'snaptrip_nightly_£', 'snaptrip_url', 'snaptrip_image',
        ) );

        foreach ( $rows as $r ) {
            fputcsv( $fh, array(
                $r->match_id, '',
                $r->confidence,
                round( (float) $r->distance_meters, 1 ),
                $r->match_method,
                $r->postcode_exact, $r->sleeps_exact, $r->candidates_considered,
                $r->cottages_com_id, $r->merchant_product_id, $r->cottages_com_title,
                $r->cottages_com_postcode, $r->cottages_com_town, $r->cottages_com_sleeps,
                $r->cottages_com_review, $r->cottages_com_weekly_price, $r->cottages_com_image,
                $r->merchant_deep_link,
                $r->snaptrip_id, $r->snaptrip_ref, $r->snaptrip_title,
                $r->snaptrip_postcode, $r->snaptrip_sleeps, $r->snaptrip_bedrooms,
                $r->snaptrip_reviews, $r->snaptrip_nightly_from, $r->snaptrip_url, $r->snaptrip_image,
            ) );
        }
        fclose( $fh );
        $this->log( 'Exported ' . count( $rows ) . ' rows to ' . $path );
        return count( $rows );
    }
}
