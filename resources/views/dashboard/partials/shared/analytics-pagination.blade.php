
    if (typeof window.setupTablePagination === 'undefined') {
        window.setupTablePagination = function(tableId, wrapperId, rowsSelector, emptyStateId, pageSize = 5) {
            var tableWrapper = document.getElementById(wrapperId);
            if(!tableWrapper) return;
            var currentPage = 1;
            var allRows = Array.from(document.querySelectorAll(rowsSelector));
            if (allRows.length === 0) return; // If no rows, skip pagination

            // Remove existing pagination if re-initialized
            if(document.getElementById(`${tableId}-pagination`)) {
                document.getElementById(`${tableId}-pagination`).remove();
            }

            // Inject pagination wrapper HTML directly below the table
            var html = `
            <div id="${tableId}-pagination" class="mt-auto hidden flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                <div class="text-sm text-gray-500 mb-3 sm:mb-0">
                    Showing <span id="${tableId}-start" class="font-bold text-gray-900">0</span> to <span id="${tableId}-end" class="font-bold text-gray-900">0</span> of <span id="${tableId}-total" class="font-bold text-gray-900">0</span> results
                </div>
                <div class="flex items-center gap-1" id="${tableId}-controls"></div>
            </div>
            `;
            tableWrapper.insertAdjacentHTML('beforeend', html);

            var paginationEl = document.getElementById(`${tableId}-pagination`);
            var emptyState = emptyStateId ? document.getElementById(emptyStateId) : null;

            function applyPagination() {
                allRows.forEach(row => row.style.display = 'none');
                
                if (allRows.length === 0) {
                    if (emptyState) emptyState.style.display = '';
                    paginationEl.classList.add('hidden');
                    paginationEl.classList.remove('flex');
                    return;
                }

                if (emptyState) emptyState.style.display = 'none';
                paginationEl.classList.remove('hidden');
                paginationEl.classList.add('flex');

                var totalPages = Math.ceil(allRows.length / pageSize);
                if (currentPage > totalPages) currentPage = totalPages;

                var start = (currentPage - 1) * pageSize;
                var end = start + pageSize;
                var paginatedRows = allRows.slice(start, end);

                paginatedRows.forEach(row => row.style.display = '');

                document.getElementById(`${tableId}-start`).textContent = start + 1;
                document.getElementById(`${tableId}-end`).textContent = Math.min(end, allRows.length);
                document.getElementById(`${tableId}-total`).textContent = allRows.length;

                renderPaginationControls(totalPages);
            }

            function renderPaginationControls(totalPages) {
                var controls = document.getElementById(`${tableId}-controls`);
                controls.innerHTML = '';

                var prevBtn = document.createElement('button');
                prevBtn.innerHTML = '<i class="fas fa-chevron-left text-xs"></i>';
                prevBtn.className = `w-8 h-8 flex items-center justify-center rounded-lg border transition-all ${currentPage === 1 ? 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a]'}`;
                prevBtn.disabled = currentPage === 1;
                prevBtn.onclick = function () { if (currentPage > 1) { currentPage--; applyPagination(); } };
                controls.appendChild(prevBtn);

                var maxVisibleButtons = 5;
                var startPage = Math.max(1, currentPage - Math.floor(maxVisibleButtons / 2));
                var endPage = Math.min(totalPages, startPage + maxVisibleButtons - 1);

                if (endPage - startPage + 1 < maxVisibleButtons) {
                    startPage = Math.max(1, endPage - maxVisibleButtons + 1);
                }

                if (startPage > 1) {
                    var firstBtn = document.createElement('button');
                    firstBtn.innerText = '1';
                    firstBtn.className = 'w-8 h-8 flex items-center justify-center rounded-lg border bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] text-sm font-medium transition-all';
                    firstBtn.onclick = function () { currentPage = 1; applyPagination(); };
                    controls.appendChild(firstBtn);

                    if (startPage > 2) {
                        var dots = document.createElement('span');
                        dots.innerHTML = '&hellip;';
                        dots.className = 'w-8 h-8 flex items-center justify-center text-gray-400 text-sm';
                        controls.appendChild(dots);
                    }
                }

                for (var i = startPage; i <= endPage; i++) {
                    var btn = document.createElement('button');
                    btn.innerText = i;
                    if (i === currentPage) {
                        btn.className = 'w-8 h-8 flex items-center justify-center rounded-lg border bg-[#a52a2a] text-white border-[#a52a2a] text-sm font-bold shadow-md shadow-[#a52a2a]/20';
                    } else {
                        btn.className = 'w-8 h-8 flex items-center justify-center rounded-lg border bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] text-sm font-medium transition-all';
                    }
                    btn.onclick = (function (page) { return function () { currentPage = page; applyPagination(); }; })(i);
                    controls.appendChild(btn);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        var dots = document.createElement('span');
                        dots.innerHTML = '&hellip;';
                        dots.className = 'w-8 h-8 flex items-center justify-center text-gray-400 text-sm';
                        controls.appendChild(dots);
                    }

                    var lastBtn = document.createElement('button');
                    lastBtn.innerText = totalPages;
                    lastBtn.className = 'w-8 h-8 flex items-center justify-center rounded-lg border bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a] text-sm font-medium transition-all';
                    lastBtn.onclick = function () { currentPage = totalPages; applyPagination(); };
                    controls.appendChild(lastBtn);
                }

                var nextBtn = document.createElement('button');
                nextBtn.innerHTML = '<i class="fas fa-chevron-right text-xs"></i>';
                nextBtn.className = `w-8 h-8 flex items-center justify-center rounded-lg border transition-all ${currentPage === totalPages || totalPages === 0 ? 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-[#a52a2a]'}`;
                nextBtn.disabled = currentPage === totalPages || totalPages === 0;
                nextBtn.onclick = function () { if (currentPage < totalPages) { currentPage++; applyPagination(); } };
                controls.appendChild(nextBtn);
            }

            applyPagination();
        };
    }

