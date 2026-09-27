document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('searchInput');
    var locationInput = document.getElementById('locationInput');
    var categorySelect = document.getElementById('categorySelect');
    var clearButton = document.getElementById('clearFilters');
    var jobItems = Array.prototype.slice.call(document.querySelectorAll('.job-item'));
    var emptyState = document.getElementById('emptyState');
    var jobCount = document.getElementById('jobCount');

    function normalize(value) {
        return (value || '').toLowerCase().trim();
    }

    function filterJobs() {
        var searchTerm = normalize(searchInput ? searchInput.value : '');
        var locationTerm = normalize(locationInput ? locationInput.value : '');
        var categoryTerm = normalize(categorySelect ? categorySelect.value : '');
        var visibleCount = 0;

        jobItems.forEach(function (item) {
            var title = normalize(item.dataset.title);
            var company = normalize(item.dataset.company);
            var location = normalize(item.dataset.location);
            var category = normalize(item.dataset.category);

            var matchesSearch = !searchTerm || title.indexOf(searchTerm) !== -1 || company.indexOf(searchTerm) !== -1;
            var matchesLocation = !locationTerm || location.indexOf(locationTerm) !== -1;
            var matchesCategory = !categoryTerm || category === categoryTerm;

            if (matchesSearch && matchesLocation && matchesCategory) {
                item.classList.remove('hidden');
                visibleCount += 1;
            } else {
                item.classList.add('hidden');
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('d-none', visibleCount !== 0);
        }

        if (jobCount) {
            jobCount.textContent = 'Showing ' + visibleCount + ' jobs';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterJobs);
    }

    if (locationInput) {
        locationInput.addEventListener('input', filterJobs);
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', filterJobs);
    }

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            if (locationInput) locationInput.value = '';
            if (categorySelect) categorySelect.value = '';
            filterJobs();
        });
    }

    var counters = Array.prototype.slice.call(document.querySelectorAll('.counter'));
    counters.forEach(function (counter) {
        var target = parseInt(counter.dataset.target, 10) || 0;
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 80));

        function tick() {
            current += step;
            if (current >= target) {
                counter.textContent = target.toLocaleString();
                return;
            }
            counter.textContent = current.toLocaleString();
            window.requestAnimationFrame(tick);
        }

        tick();
    });

    filterJobs();
});
