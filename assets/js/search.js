document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const searchForm = document.getElementById('searchForm');

    if (!searchInput || !searchSuggestions || !searchForm) return;

    let debounceTimeout;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimeout);
        const query = searchInput.value.trim();

        if (query.length >= 3) {
            debounceTimeout = setTimeout(() => {
                fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchSuggestions.innerHTML = '';
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }
                        if (data.libros.length === 0 && data.autores.length === 0) {
                            searchSuggestions.classList.remove('show');
                            return;
                        }

                        // Sugerencias de libros
                        if (data.libros.length > 0) {
                            const librosHeader = document.createElement('div');
                            librosHeader.className = 'suggestion-item suggestion-header';
                            librosHeader.textContent = 'Libros';
                            searchSuggestions.appendChild(librosHeader);

                            data.libros.forEach(book => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.className = 'suggestion-item';
                                suggestionItem.innerHTML = `
                                    <strong>${book.titulo}</strong> <small class="text-muted">por ${book.autor}</small>
                                `;
                                suggestionItem.addEventListener('click', () => {
                                    searchInput.value = book.titulo;
                                    searchSuggestions.innerHTML = '';
                                    searchSuggestions.classList.remove('show');
                                    searchForm.action = `explorar.php?q=${encodeURIComponent(book.titulo)}&tipo=titulo`;
                                    searchForm.submit();
                                });
                                searchSuggestions.appendChild(suggestionItem);
                            });
                        }

                        // Sugerencias de autores
                        if (data.autores.length > 0) {
                            const autoresHeader = document.createElement('div');
                            autoresHeader.className = 'suggestion-item suggestion-header';
                            autoresHeader.textContent = 'Autores';
                            searchSuggestions.appendChild(autoresHeader);

                            data.autores.forEach(autor => {
                                const suggestionItem = document.createElement('div');
                                suggestionItem.className = 'suggestion-item';
                                suggestionItem.innerHTML = `<strong>${autor}</strong>`;
                                suggestionItem.addEventListener('click', () => {
                                    searchInput.value = autor;
                                    searchSuggestions.innerHTML = '';
                                    searchSuggestions.classList.remove('show');
                                    searchForm.action = `explorar.php?q=${encodeURIComponent(autor)}&tipo=autor`;
                                    searchForm.submit();
                                });
                                searchSuggestions.appendChild(suggestionItem);
                            });
                        }

                        searchSuggestions.classList.add('show');
                    })
                    .catch(error => {
                        console.error('Error al obtener sugerencias:', error);
                        searchSuggestions.classList.remove('show');
                    });
            }, 300);
        } else {
            searchSuggestions.innerHTML = '';
            searchSuggestions.classList.remove('show');
        }
    });

    // Ocultar sugerencias al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.classList.remove('show');
        }
    });

    // Enviar formulario al presionar Enter
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                searchForm.action = `explorar.php?q=${encodeURIComponent(query)}&tipo=titulo`;
                searchForm.submit();
            }
        }
    });
});