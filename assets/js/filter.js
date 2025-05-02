document.getElementById('toggleFilters').addEventListener('click', function() {
    const sidebarFiltros = document.getElementById('sidebarFiltros');
    const booksContainer = document.getElementById('booksContainer');
    sidebarFiltros.classList.toggle('abierto');
    booksContainer.classList.toggle('filtros-abiertos');
});

document.addEventListener('DOMContentLoaded', () => {
    const genreMap = {
        programacion: 'Programación',
        desarrollo_web: 'Desarrollo Web',
        desarrollo_aplicaciones: 'Desarrollo de Aplicaciones',
        ciberseguridad: 'Ciberseguridad',
        bases_datos: 'Bases de Datos',
        inteligencia_artificial: 'IA/Machine Learning',
        cloud_computing: 'Cloud Computing',
        devops_automatizacion: 'DevOps y Automatización',
        diseno_ux_ui: 'Diseño UX/UI'
    };

    const filters = document.querySelectorAll('.filter-checkbox');
    const booksContainer = document.getElementById('booksContainer');
    const allBooks = Array.from(booksContainer.children);
    const sortSelect = document.getElementById('sortSelect');
    const pagination = document.getElementById('pagination');
    const resultsCounter = document.getElementById('resultsCounter');
    const booksPerPage = 20;
    let currentPage = 1;

    function renderBooks(filteredBooks) {
        booksContainer.innerHTML = '';
        const start = (currentPage - 1) * booksPerPage;
        const end = start + booksPerPage;
        const paginatedBooks = filteredBooks.slice(start, end);

        if (paginatedBooks.length === 0 && filteredBooks.length === 0) {
            booksContainer.innerHTML = '<p class="text-center text-muted py-4">No se encontraron libros con los filtros seleccionados.</p>';
        } else {
            paginatedBooks.forEach(book => {
                const category = book.dataset.category;
                const readableCategory = genreMap[category] || category;
                const libroId = book.dataset.id;
                const isFavorite = deseados.includes(parseInt(libroId));

                const bookElement = document.createElement('a');
                bookElement.href = `libro.php?id=${libroId}`;
                bookElement.className = 'book-item list-group-item list-group-item-action d-flex align-items-center p-3';
                bookElement.dataset.id = libroId;
                bookElement.dataset.title = book.dataset.title;
                bookElement.dataset.author = book.dataset.author;
                bookElement.dataset.description = book.dataset.description;
                bookElement.dataset.category = category;
                bookElement.dataset.image = book.dataset.image;
                bookElement.innerHTML = `
                    <img src="${book.dataset.image}" class="me-3" alt="Libro" style="width: 180px; height: 225px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-1">${book.dataset.title}</h5>
                            <i class="toggle-favorite ${isFavorite ? 'fas' : 'far'} fa-heart" data-id="${libroId}"></i>
                        </div>
                        <p class="text-muted mb-1">Por ${book.dataset.author}</p>
                        <p class="text-muted mb-2">${book.dataset.description}</p>
                        <span class="badge bg-dark">${readableCategory}</span>
                    </div>
                `;
                booksContainer.appendChild(bookElement);
            });
        }

        // Asignar eventos a los íconos de favoritos
        const favoriteIcons = document.querySelectorAll('.toggle-favorite');
        favoriteIcons.forEach(icon => {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const libroId = parseInt(this.getAttribute('data-id'));
                const isFavorite = this.classList.contains('fas');
        
                fetch('explorar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ libro_id: libroId, action: isFavorite ? 'remove' : 'add' })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (isFavorite) {
                            this.classList.remove('fas');
                            this.classList.add('far');
                            const index = deseados.indexOf(libroId);
                            if (index !== -1) {
                                deseados.splice(index, 1);
                            }
                            localStorage.setItem('deseadoRemoved', JSON.stringify({
                                libroId: libroId,
                                timestamp: Date.now()
                            }));
                        } else {
                            this.classList.remove('far');
                            this.classList.add('fas');
                            deseados.push(libroId);
                            localStorage.setItem('deseadoAdded', JSON.stringify({
                                libroId: libroId,
                                timestamp: Date.now()
                            }));
                            // Animar el corazón del navbar
                            const navHeart = document.getElementById('navHeart') || document.getElementById('navHeartMobile');
                            if (navHeart) {
                                navHeart.classList.add('nav-heart-animate');
                                setTimeout(() => {
                                    navHeart.classList.remove('nav-heart-animate');
                                }, 500);
                            }
                        }
                    } else {
                        if (data.message === 'Por favor, inicia sesión para agregar libros a tus deseados') {
                            alert(data.message);
                            window.location.href = 'login.php';
                        } else {
                            console.error('Error:', data.message);
                            alert('Error: ' + (data.message || 'No se pudo actualizar la lista de deseados.'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud: ' + error.message);
                });
            });
        });

        resultsCounter.textContent = `${filteredBooks.length} resultados`;
    }

    function renderPagination(totalBooks) {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(totalBooks / booksPerPage);

        const prevItem = document.createElement('li');
        prevItem.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevItem.innerHTML = `<a class="page-link" href="#">Anterior</a>`;
        prevItem.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                applyFiltersAndSort();
            }
        });
        pagination.appendChild(prevItem);

        for (let i = 1; i <= totalPages; i++) {
            const pageItem = document.createElement('li');
            pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageItem.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            pageItem.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                applyFiltersAndSort();
            });
            pagination.appendChild(pageItem);
        }

        const nextItem = document.createElement('li');
        nextItem.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextItem.innerHTML = `<a class="page-link" href="#">Siguiente</a>`;
        nextItem.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                applyFiltersAndSort();
            }
        });
        pagination.appendChild(nextItem);
    }

    function applyFiltersAndSort() {
        const activeFilters = Array.from(filters)
            .filter(filter => filter.checked)
            .map(filter => filter.value);

        let filteredBooks = allBooks.filter(book => {
            const category = book.dataset.category;
            return activeFilters.length === 0 || activeFilters.includes(category);
        });

        const sortValue = sortSelect.value;
        if (sortValue === 'recientes') {
            filteredBooks.sort((a, b) => parseInt(b.dataset.id) - parseInt(a.dataset.id));
        } else if (sortValue === 'titulo_asc') {
            filteredBooks.sort((a, b) => a.dataset.title.localeCompare(b.dataset.title));
        } else if (sortValue === 'titulo_desc') {
            filteredBooks.sort((a, b) => b.dataset.title.localeCompare(a.dataset.title));
        }

        renderBooks(filteredBooks);
        renderPagination(filteredBooks.length);
    }

    // Aplicar filtro de categoría desde URL
    if (categoriaFiltro) {
        const filterCheckbox = document.getElementById(categoriaFiltro);
        if (filterCheckbox) {
            filterCheckbox.checked = true;
        }
    }

    filters.forEach(filter => filter.addEventListener('change', applyFiltersAndSort));
    sortSelect.addEventListener('change', applyFiltersAndSort);

    applyFiltersAndSort();
});