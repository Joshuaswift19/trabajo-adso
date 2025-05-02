let sortField = "titulo";
let sortReverse = false;
let allBooks = [];
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

function renderTable(data) {
    const tbody = document.getElementById("customerTableBody");
    if (!tbody) return console.error('Elemento customerTableBody no encontrado');
    tbody.innerHTML = "";
    data.forEach(documento => {
        const enlaceTexto = documento.enlace && !documento.enlace.startsWith('http') ? documento.enlace.split('/').pop() : documento.enlace;
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${documento.id}</td>
            <td><img src="${documento.imagen_url || '/placeholder.jpg'}" alt="Imagen" style="width: 50px; height: auto;"></td>
            <td>${documento.titulo}</td>
            <td>${documento.autor}</td>
            <td>${genreMap[documento.categoria] || documento.categoria}</td>
            <td>${documento.descripcion}</td>
            <td>${documento.publication_date || '-'}</td>
            <td>${documento.enlace ? `<a href="${documento.enlace}" target="_blank">${enlaceTexto}</a>` : '-'}</td>
            <td class="action-buttons">
                <button class="btn btn-primary btn-sm edit-btn" data-id="${documento.id}"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteCustomer(${documento.id})"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateTotalBooks(filteredBooks) {
    document.getElementById('totalBooksValue').textContent = filteredBooks.length;
    document.getElementById('totalBooksPrev').textContent = `from ${allBooks.length}`;
}

function fetchDocumentos(field = sortField, reverse = sortReverse, query = "") {
    fetch(`/dashboard-admin.php?action=get&sort=${field}&reverse=${reverse ? 1 : 0}&search=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                if (!query) allBooks = data;
                data.sort((a, b) => {
                    if (field === "autor") return reverse ? b.autor.localeCompare(a.autor) : a.autor.localeCompare(b.autor);
                    if (field === "categoria") return reverse ? b.categoria.localeCompare(a.categoria) : a.categoria.localeCompare(b.categoria);
                    if (field === "id") return reverse ? b.id - a.id : a.id - b.id; // Ordenar por ID
                    return reverse ? b.titulo.localeCompare(a.titulo) : a.titulo.localeCompare(b.titulo);
                });
                renderTable(data);
                updateTotalBooks(data);
            } else {
                console.error('Respuesta no es un array:', data);
                alert('Error al cargar documentos');
            }
        })
        .catch(error => {
            console.error('Error fetching documentos:', error);
            alert('Error al cargar documentos: ' + error.message);
            document.getElementById('totalBooksValue').textContent = 'Error';
        });
}

function deleteCustomer(id) {
    if (confirm("¿Seguro que quieres eliminar este documento?")) {
        fetch('/dashboard-admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                fetchDocumentos();
            } else throw new Error(data.message);
        })
        .catch(error => alert('Error al eliminar documento: ' + error.message));
    }
}

function searchBooks() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    fetchDocumentos(sortField, sortReverse, query);
}

function openUploadModal() {
    $('#uploadModal').modal('show');
}

function openLinkModal() {
    $('#linkModal').modal('show');
}

function uploadImage() {
    const fileInput = document.getElementById('imageFileInput');
    const formImageInput = document.getElementById('formImageInput');
    if (fileInput.files.length > 0) {
        formImageInput.files = fileInput.files;
        document.getElementById('imageInput').textContent = fileInput.files[0].name;
    }
    $('#uploadModal').modal('hide');
}

function submitLink() {
    const fileInput = document.getElementById('linkFileInput');
    const urlInput = document.getElementById('linkUrlInput');
    const formFileInput = document.getElementById('formLinkFileInput');
    const formUrlInput = document.getElementById('formLinkUrlInput');
    if (fileInput.files.length > 0) {
        formFileInput.files = fileInput.files;
        formUrlInput.value = '';
        document.getElementById('linkInput').textContent = fileInput.files[0].name;
    } else if (urlInput.value) {
        formUrlInput.value = urlInput.value;
        formFileInput.value = '';
        document.getElementById('linkInput').textContent = urlInput.value;
    }
    $('#linkModal').modal('hide');
}

function clearImageSelection() {
    document.getElementById('formImageInput').value = '';
    document.getElementById('imageInput').textContent = 'Selecciona una imagen';
}

function clearLinkSelection() {
    document.getElementById('formLinkFileInput').value = '';
    document.getElementById('formLinkUrlInput').value = '';
    document.getElementById('linkInput').textContent = 'Seleccione un archivo o enlace';
}

document.addEventListener('DOMContentLoaded', function() {
    const addCustomerForm = document.getElementById('addCustomerForm');
    const genreSelect = document.getElementById('genreSelect');

    if (addCustomerForm) {
        addCustomerForm.addEventListener('submit', function(event) {
            event.preventDefault();
            if (!genreSelect.value) {
                alert('Debe seleccionar un género antes de enviar el formulario.');
                return;
            }
            const formData = new FormData(this);
            const formImageInput = document.getElementById('formImageInput');
            const formLinkFileInput = document.getElementById('formLinkFileInput');
            const formLinkUrlInput = document.getElementById('formLinkUrlInput');
            if (formImageInput.files.length) formData.set('imagen', formImageInput.files[0]);
            if (formLinkFileInput.files.length) {
                formData.set('enlace_file', formLinkFileInput.files[0]);
                formData.delete('enlace_url');
            } else if (formLinkUrlInput.value) {
                formData.set('enlace_url', formLinkUrlInput.value);
                formData.delete('enlace_file');
            }
            fetch('/dashboard-admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json().catch(() => {
                return response.text().then(text => {
                    throw new Error(`Respuesta no es JSON: ${text}`);
                });
            }))
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
                    addCustomerForm.reset();
                    clearImageSelection();
                    clearLinkSelection();
                    alert(data.message);
                    fetchDocumentos();
                } else alert('Error: ' + data.message);
            })
            .catch(error => {
                console.error('Error adding documento:', error);
                alert('Error al agregar el documento: ' + error.message);
            });
        });
    }

    const sortSelect = document.getElementById("sortSelect");
    const toggleSortBtn = document.getElementById("toggleSortBtn");
    const searchInput = document.getElementById("searchInput");
    const addCustomerModal = document.getElementById("addCustomerModal");

    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            sortField = e.target.value;
            fetchDocumentos(sortField, sortReverse);
        });
    }
    if (toggleSortBtn) {
        toggleSortBtn.addEventListener("click", () => {
            sortReverse = !sortReverse;
            toggleSortBtn.innerHTML = `<i class="bi bi-sort-alpha-${sortReverse ? 'up' : 'down'}"></i>`;
            fetchDocumentos(sortField, sortReverse);
        });
    }
    if (searchInput) searchInput.addEventListener("input", searchBooks);
    if (addCustomerModal && addCustomerForm) {
        const modalInstance = new bootstrap.Modal(addCustomerModal);
        addCustomerModal.addEventListener("hidden.bs.modal", () => addCustomerForm.reset());
        const cancelButton = addCustomerForm.querySelector(".btn-outline-secondary");
        if (cancelButton) cancelButton.addEventListener("click", () => {
            modalInstance.hide();
            addCustomerForm.reset();
        });
    }

    const hamburgerButton = document.getElementById('hamburgerButton');
    const sidebar = document.getElementById('customSidebar');
    const backdrop = document.getElementById('backdrop');
    if (hamburgerButton && sidebar && backdrop) {
        hamburgerButton.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            hamburgerButton.classList.toggle('active');
            backdrop.classList.toggle('active');
        });
    }

    document.getElementById("customerTableBody").addEventListener("click", function(e) {
        if (e.target.closest(".edit-btn")) {
            const id = e.target.closest(".edit-btn").getAttribute("data-id");
            fetch(`/dashboard-admin.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const doc = data[0];
                        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                        modal.show();
                        document.getElementById('editCustomerModal').addEventListener('shown.bs.modal', function() {
                            const form = document.getElementById('editCustomerForm');
                            form.querySelector('input[name="id"]').value = doc.id || '';
                            form.querySelector('input[name="titulo"]').value = doc.titulo || '';
                            form.querySelector('input[name="categoria"]').value = doc.categoria || '';
                            form.querySelector('input[name="autor"]').value = doc.autor || '';
                            form.querySelector('textarea[name="descripcion"]').value = doc.descripcion || '';
                            form.querySelector('input[name="publication_date"]').value = doc.publication_date || '';
                        }, { once: true });
                    } else alert("Documento no encontrado.");
                })
                .catch(error => alert("Error al cargar datos del documento: " + error));
        }
    });document.getElementById("customerTableBody").addEventListener("click", function(e) {
        if (e.target.closest(".edit-btn")) {
            const id = e.target.closest(".edit-btn").getAttribute("data-id");
            fetch(`/dashboard-admin.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const doc = data[0];
                        const modal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                        modal.show();
                        document.getElementById('editCustomerModal').addEventListener('shown.bs.modal', function() {
                            const form = document.getElementById('editCustomerForm');
                            form.querySelector('input[name="id"]').value = doc.id || '';
                            form.querySelector('input[name="titulo"]').value = doc.titulo || '';
                            form.querySelector('select[name="categoria"]').value = doc.categoria || ''; // Cambiado a select
                            form.querySelector('input[name="autor"]').value = doc.autor || '';
                            form.querySelector('textarea[name="descripcion"]').value = doc.descripcion || '';
                            form.querySelector('input[name="publication_date"]').value = doc.publication_date || '';
                        }, { once: true });
                    } else alert("Documento no encontrado.");
                })
                .catch(error => alert("Error al cargar datos del documento: " + error));
        }
    });

    const editForm = document.getElementById("editCustomerForm");
    if (editForm) {
        editForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            formData.append("action", "edit");
            fetch('/dashboard-admin.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById("editCustomerModal")).hide();
                    fetchDocumentos();
                } else alert("Error: " + data.message);
            })
            .catch(error => alert("Error al actualizar: " + error.message));
        });
    }

    fetchDocumentos();
});

// Tiempo máximo de inactividad en milisegundos (10 minutos = 600000 ms)
const tiempoMaximoInactividad = 600000; // 10 minutos
let tiempoInactividad = 0;
let sesionCerrada = false; // Bandera para evitar múltiples mensajes

// Reiniciar el temporizador de inactividad al detectar actividad del usuario
function reiniciarTemporizador() {
    if (!sesionCerrada) {
        tiempoInactividad = 0; // Reinicia el tiempo de inactividad
    }
}

// Incrementar el tiempo de inactividad y verificar con el servidor
function verificarInactividad() {
    if (sesionCerrada) return; // Si la sesión ya está cerrada, no hacer nada

    tiempoInactividad += 1000; // Incrementar cada segundo
    if (tiempoInactividad >= tiempoMaximoInactividad) {
        // Hacer una solicitud al servidor para verificar el estado de la sesión
        fetch('index.php', { method: 'POST' })
            .then(response => {
                if (response.redirected) {
                    // Si el servidor redirige, mostrar el mensaje y redirigir al usuario
                    sesionCerrada = true;
                    alert("Sesión cerrada por inactividad.");
                    window.location.href = response.url; // Redirigir a index.php
                }
            })
            .catch(error => {
                console.error('Error al verificar la sesión:', error);
            });
    }
}

// Detectar actividad del usuario
window.onload = reiniciarTemporizador;
window.onmousemove = reiniciarTemporizador;
window.onkeypress = reiniciarTemporizador;
window.onclick = reiniciarTemporizador;
window.onscroll = reiniciarTemporizador;

// Verificar inactividad cada segundo
setInterval(verificarInactividad, 1000);