// The University of Raparin's header, shared by the public site and the
// administration panel. Without it the header still works: the language list
// is a <details>, the search icon is a link, and the phone menu is shown open.
(function(){
    var header = document.getElementById('site-header');
    if (!header) return;

    var menuToggle = header.querySelector('.menu-toggle');
    var mobileNav = document.getElementById('mobile-nav');
    if (menuToggle && mobileNav) {
        menuToggle.hidden = false;
        menuToggle.addEventListener('click', function () {
            var open = mobileNav.classList.toggle('is-open');
            menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        // A link to a section of the same page (#feedback) does not leave
        // the page, so the menu has to close itself or it covers what was asked for.
        mobileNav.addEventListener('click', function (event) {
            if (event.target.closest('a')) closeMenu();
        });
    }
    function closeMenu() {
        if (!mobileNav || !mobileNav.classList.contains('is-open')) return false;
        mobileNav.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        return true;
    }

    var searchToggle = header.querySelector('.search-toggle');
    var searchForm = document.getElementById('site-search');
    if (searchToggle && searchForm) {
        searchToggle.addEventListener('click', function (event) {
            event.preventDefault();
            var opening = searchForm.hidden;
            searchForm.hidden = !opening;
            searchToggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (opening) searchForm.querySelector('input').focus();
        });
    }

    var language = header.querySelector('.lang');
    document.addEventListener('click', function (event) {
        if (language && language.open && !language.contains(event.target)) language.open = false;
    });
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        if (language) language.open = false;
        if (closeMenu()) menuToggle.focus();
        if (searchForm && !searchForm.hidden) {
            searchForm.hidden = true;
            searchToggle.setAttribute('aria-expanded', 'false');
            searchToggle.focus();
        }
    });

    // Past the first screenful the top row slides away and the main row turns solid.
    function onScroll() { header.classList.toggle('is-scrolled', window.scrollY > 60); }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
})();
