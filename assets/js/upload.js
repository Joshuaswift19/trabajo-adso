const imageFileInput = document.getElementById('imageFileInput');
const formImageInput = document.getElementById('formImageInput');
const imageSelectedFilesDiv = document.getElementById('imageSelectedFiles');
const imageInputButton = document.getElementById('imageInput');
const imageClearIcon = document.getElementById('imageClearIcon');
const uploadContainer = document.getElementById('uploadContainer');

const linkFileInput = document.getElementById('linkFileInput');
const formLinkFileInput = document.getElementById('formLinkFileInput');
const formLinkUrlInput = document.getElementById('formLinkUrlInput');
const linkUrlInput = document.getElementById('linkUrlInput');
const linkFileSelectedFilesDiv = document.getElementById('linkFileSelectedFiles');
const linkInputButton = document.getElementById('linkInput');
const linkClearIcon = document.getElementById('linkClearIcon');
const linkFileContainer = document.getElementById('linkFileContainer');

const addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'), { backdrop: 'static' });
const uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'), { backdrop: 'static' });
const linkModal = new bootstrap.Modal(document.getElementById('linkModal'), { backdrop: 'static' });

// Abrir modales
function openUploadModal() {
    console.log('Abriendo modal de imagen');
    imageFileInput.value = '';
    updateImageSelectedFiles();
    uploadModal.show();
}

function openLinkModal() {
    console.log('Abriendo modal de enlace');
    linkFileInput.value = '';
    linkUrlInput.value = '';
    updateLinkFileSelectedFiles();
    linkModal.show();
}

// Actualizar visualización de imagen
function updateImageSelectedFiles() {
    console.log('Actualizando imageSelectedFilesDiv, archivos:', formImageInput.files.length);
    imageSelectedFilesDiv.innerHTML = formImageInput.files.length ? `<span>${formImageInput.files[0].name}</span>` : '<span>No hay imagen seleccionada</span>';
    imageInputButton.textContent = formImageInput.files.length ? formImageInput.files[0].name : 'Selecciona una imagen';
    imageClearIcon.style.display = formImageInput.files.length ? 'inline-block' : 'none';
    // Actualizar vista dentro del modal
    const modalFilesDiv = document.querySelector('#uploadModal .selected-files');
    modalFilesDiv.innerHTML = imageFileInput.files.length ? `<span>${imageFileInput.files[0].name}</span>` : '<span>No hay imagen seleccionada</span>';
}

// Actualizar visualización de archivo o URL
function updateLinkFileSelectedFiles() {
    console.log('Actualizando linkFileSelectedFilesDiv, archivos:', formLinkFileInput.files.length, 'URL:', formLinkUrlInput.value);
    if (formLinkFileInput.files.length) {
        linkFileSelectedFilesDiv.innerHTML = `<span>${formLinkFileInput.files[0].name}</span>`;
        linkInputButton.textContent = formLinkFileInput.files[0].name;
    } else if (formLinkUrlInput.value) {
        linkFileSelectedFilesDiv.innerHTML = `<span>${formLinkUrlInput.value}</span>`;
        linkInputButton.textContent = formLinkUrlInput.value;
    } else {
        linkFileSelectedFilesDiv.innerHTML = `<span>No hay archivo o URL seleccionada</span>`;
        linkInputButton.textContent = 'Seleccione un archivo o enlace';
    }
    linkClearIcon.style.display = (formLinkFileInput.files.length || formLinkUrlInput.value) ? 'inline-block' : 'none';
    // Actualizar vista dentro del modal
    const modalFilesDiv = document.querySelector('#linkModal .selected-files');
    if (linkFileInput.files.length) {
        modalFilesDiv.innerHTML = `<span>${linkFileInput.files[0].name}</span>`;
    } else if (linkUrlInput.value) {
        modalFilesDiv.innerHTML = `<span>${linkUrlInput.value}</span>`;
    } else {
        modalFilesDiv.innerHTML = `<span>No hay archivo o URL seleccionada</span>`;
    }
}

// Manejar selección de imagen
function uploadImage() {
    console.log('uploadImage: Archivos seleccionados:', imageFileInput.files.length);
    if (!imageFileInput.files.length) {
        alert('Debe seleccionar una imagen.');
        console.log('No se seleccionó imagen');
        return;
    }
    formImageInput.files = imageFileInput.files;
    console.log('Imagen transferida:', formImageInput.files[0]?.name, 'Tamaño:', formImageInput.files[0]?.size);
    updateImageSelectedFiles();
    uploadModal.hide();
}

// Manejar selección de archivo o URL
function submitLink() {
    console.log('submitLink: Archivos:', linkFileInput.files.length, 'URL:', linkUrlInput.value);
    if (!linkFileInput.files.length && !linkUrlInput.value) {
        alert('Debe seleccionar un archivo o ingresar una URL válida.');
        console.log('No se seleccionó archivo ni URL');
        return;
    }
    if (linkFileInput.files.length) {
        formLinkFileInput.files = linkFileInput.files;
        formLinkUrlInput.value = '';
        console.log('Archivo transferido:', formLinkFileInput.files[0]?.name, 'Tamaño:', formLinkFileInput.files[0]?.size);
    } else {
        formLinkUrlInput.value = linkUrlInput.value;
        formLinkFileInput.value = '';
        linkFileInput.value = '';
    }
    updateLinkFileSelectedFiles();
    linkModal.hide();
}

// Limpiar selecciones
window.clearImageSelection = function() {
    console.log('Limpiando imagen');
    formImageInput.value = '';
    imageFileInput.value = '';
    updateImageSelectedFiles();
};

window.clearImageModalSelection = function() {
    console.log('Limpiando modal de imagen');
    imageFileInput.value = '';
    updateImageSelectedFiles();
};

window.clearLinkSelection = function() {
    console.log('Limpiando enlace');
    formLinkFileInput.value = '';
    formLinkUrlInput.value = '';
    linkFileInput.value = '';
    linkUrlInput.value = '';
    updateLinkFileSelectedFiles();
};

window.clearLinkModalSelection = function() {
    console.log('Limpiando modal de enlace');
    linkFileInput.value = '';
    linkUrlInput.value = '';
    updateLinkFileSelectedFiles();
};

// Listeners para cambios
imageFileInput.addEventListener('change', () => {
    console.log('Cambio en imageFileInput:', imageFileInput.files.length);
    updateImageSelectedFiles();
});

linkFileInput.addEventListener('change', () => {
    console.log('Cambio en linkFileInput:', linkFileInput.files.length);
    updateLinkFileSelectedFiles();
});

linkUrlInput.addEventListener('input', () => {
    console.log('Cambio en linkUrlInput:', linkUrlInput.value);
    updateLinkFileSelectedFiles();
});

// Drag-and-drop para imagen
uploadContainer.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadContainer.style.backgroundColor = '#e9ecef';
});

uploadContainer.addEventListener('dragleave', (e) => {
    e.preventDefault();
    uploadContainer.style.backgroundColor = '';
});

uploadContainer.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadContainer.style.backgroundColor = '';
    const files = e.dataTransfer.files;
    if (files.length === 1 && files[0].type.startsWith('image/')) {
        imageFileInput.files = files;
        console.log('Imagen soltada:', imageFileInput.files[0]?.name, 'Tamaño:', imageFileInput.files[0]?.size);
        updateImageSelectedFiles();
    } else {
        alert('Por favor, arrastra solo una imagen (JPG, PNG, GIF).');
    }
});

// Drag-and-drop para archivo
linkFileContainer.addEventListener('dragover', (e) => {
    e.preventDefault();
    linkFileContainer.style.backgroundColor = '#e9ecef';
});

linkFileContainer.addEventListener('dragleave', (e) => {
    e.preventDefault();
    linkFileContainer.style.backgroundColor = '';
});

linkFileContainer.addEventListener('drop', (e) => {
    e.preventDefault();
    linkFileContainer.style.backgroundColor = '';
    const files = e.dataTransfer.files;
    if (files.length === 1 && /\.(pdf|doc|docx|txt)$/i.test(files[0].name)) {
        linkFileInput.files = files;
        console.log('Archivo soltado:', linkFileInput.files[0]?.name, 'Tamaño:', linkFileInput.files[0]?.size);
        updateLinkFileSelectedFiles();
    } else {
        alert('Por favor, arrastra solo un archivo PDF, DOC, DOCX o TXT.');
    }
});