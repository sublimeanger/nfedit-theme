/* nfedit Cottages filter — progressive enhancement (REST swap, URL sync, load more) */

(function () {
    var grid = document.querySelector('[data-cottages-grid]');
    if (grid) {
        var resultsEl   = grid.querySelector('[data-cottages-results]');
        var countEl     = grid.querySelector('[data-cottages-count]');
        var pills       = grid.querySelectorAll('[data-pill-id]');
        var sortSelect  = grid.querySelector('[data-cottages-sort]');
        var loadMoreBtn = grid.querySelector('[data-cottages-load-more]');

        if (resultsEl) {
            var REST_BASE = '/wp-json/nfedit/v1/cottages';
            var scopeArea    = grid.getAttribute('data-scope-area') || '';
            var scopeFeature = grid.getAttribute('data-scope-feature') || '';

            function readState() {
                var url = new URL(window.location.href);
                var qp = url.searchParams;
                var state = {
                    area:         qp.get('area') || scopeArea || '',
                    feature:      qp.get('feature') || '',
                    forest_coast: qp.get('forest_coast') || '',
                    dogs:         qp.get('dogs') || '',
                    min_sleeps:   qp.get('min_sleeps') || '',
                    sort:         qp.get('sort') || (sortSelect ? sortSelect.value : 'editor'),
                    paged:        parseInt(qp.get('paged') || '1', 10) || 1,
                };
                if (scopeFeature) {
                    var f = (state.feature ? state.feature.split(',') : []).filter(Boolean);
                    if (f.indexOf(scopeFeature) === -1) f.push(scopeFeature);
                    state.feature = f.join(',');
                }
                return state;
            }

            function writeURL(state) {
                var url = new URL(window.location.href);
                var sp = url.searchParams;

                var f = state.feature;
                if (scopeFeature && f) {
                    f = f.split(',').filter(function (x) { return x !== scopeFeature; }).join(',');
                }

                function update(k, v) {
                    if (v && v !== '' && v !== '0') sp.set(k, v);
                    else sp.delete(k);
                }

                update('feature', f);
                update('forest_coast', state.forest_coast);
                update('dogs',         state.dogs);
                update('min_sleeps',   state.min_sleeps);
                update('sort',         state.sort === 'editor' ? '' : state.sort);
                if (state.paged > 1) sp.set('paged', state.paged); else sp.delete('paged');

                if (!scopeArea) update('area', state.area);

                history.pushState({}, '', url.toString());
            }

            function renderPills(state) {
                pills.forEach(function (pill) {
                    var param = pill.getAttribute('data-pill-param');
                    var value = pill.getAttribute('data-pill-value');
                    var isActive = false;
                    if (param === 'feature') {
                        var f = (state.feature || '').split(',').filter(Boolean);
                        isActive = f.indexOf(value) !== -1;
                    } else {
                        isActive = String(state[param] || '') === String(value);
                    }
                    pill.classList.toggle('is-active', isActive);
                });
            }

            var lastRequest = 0;
            function fetchResults(state, append) {
                grid.classList.add('is-loading');
                var reqId = ++lastRequest;
                var params = new URLSearchParams();
                Object.keys(state).forEach(function (k) {
                    if (state[k] !== '' && state[k] !== null && state[k] !== undefined) {
                        params.set(k, state[k]);
                    }
                });
                fetch(REST_BASE + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                }).then(function (res) {
                    if (!res.ok) throw new Error('REST error: ' + res.status);
                    return res.json();
                }).then(function (data) {
                    if (reqId !== lastRequest) return;

                    if (append) {
                        var tmp = document.createElement('div');
                        tmp.innerHTML = data.html;
                        var newItems = tmp.querySelector('.nfedit-cottages-grid__items');
                        var existing = resultsEl.querySelector('.nfedit-cottages-grid__items');
                        if (newItems && existing) {
                            while (newItems.firstChild) {
                                existing.appendChild(newItems.firstChild);
                            }
                        }
                    } else {
                        resultsEl.innerHTML = data.html;
                    }
                    if (countEl) {
                        countEl.textContent = data.total + ' cottage' + (data.total === 1 ? '' : 's');
                    }
                    if (loadMoreBtn) {
                        if (data.paged < data.max_pages) {
                            loadMoreBtn.style.display = '';
                            loadMoreBtn.setAttribute('data-next-page', String(data.paged + 1));
                            loadMoreBtn.setAttribute('data-max-pages', String(data.max_pages));
                        } else {
                            loadMoreBtn.style.display = 'none';
                        }
                    }
                }).catch(function (err) {
                    console.error('[nfedit] cottage filter fetch failed:', err);
                }).then(function () {
                    grid.classList.remove('is-loading');
                });
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function (e) {
                    e.preventDefault();
                    var param = pill.getAttribute('data-pill-param');
                    var value = pill.getAttribute('data-pill-value');
                    var state = readState();

                    if (param === 'feature') {
                        var f = (state.feature || '').split(',').filter(Boolean);
                        var idx = f.indexOf(value);
                        if (idx > -1) f.splice(idx, 1); else f.push(value);
                        state.feature = f.join(',');
                    } else {
                        if (String(state[param]) === String(value)) {
                            state[param] = '';
                        } else {
                            state[param] = value;
                        }
                    }
                    state.paged = 1;
                    writeURL(state);
                    renderPills(state);
                    fetchResults(state, false);
                });
            });

            if (sortSelect) {
                sortSelect.addEventListener('change', function () {
                    var state = readState();
                    state.sort = sortSelect.value;
                    state.paged = 1;
                    writeURL(state);
                    fetchResults(state, false);
                });
            }

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var state = readState();
                    state.paged = parseInt(loadMoreBtn.getAttribute('data-next-page'), 10) || (state.paged + 1);
                    writeURL(state);
                    fetchResults(state, true);
                });
            }

            window.addEventListener('popstate', function () {
                var state = readState();
                renderPills(state);
                fetchResults(state, false);
            });
        }
    }

    // ─── Collections tabs (separate component, same JS file) ───
    var tabsEl = document.querySelector('[data-collections-tabs]');
    if (tabsEl) {
        var buttons = tabsEl.querySelectorAll('[data-tab-id]');
        var panels  = document.querySelectorAll('[data-tab-panel]');

        function activate(id) {
            buttons.forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-tab-id') === id); });
            panels.forEach(function (p) {
                var match = p.getAttribute('data-tab-panel') === id;
                p.classList.toggle('is-active', match);
                if (match) p.removeAttribute('hidden'); else p.setAttribute('hidden', '');
            });
        }

        buttons.forEach(function (b) {
            b.addEventListener('click', function () {
                var id = b.getAttribute('data-tab-id');
                activate(id);
                history.replaceState(null, '', '#' + id);
            });
        });

        var hash = (window.location.hash || '').replace('#', '');
        if (hash && document.querySelector('[data-tab-panel="' + hash + '"]')) {
            activate(hash);
        }
    }
})();
