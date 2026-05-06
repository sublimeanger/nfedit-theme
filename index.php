<?php
/**
 * Fallback template. Phase 2 replaces with proper header/footer wrappers.
 * Phase 4-10 add specific single-{cpt}.php / page-{slug}.php templates.
 */
get_header();
?>
<main class="nfedit-main" id="main">
    <div class="container-edit" style="padding: 6rem 1.5rem;">
        <h1 style="font-family: Georgia, serif;">The New Forest Edit</h1>
        <p>Phase 1 scaffold. Phase 2 brings design.</p>
    </div>
</main>
<?php get_footer();
