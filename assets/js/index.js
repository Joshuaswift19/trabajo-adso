document.addEventListener("DOMContentLoaded", function () {
  let mensaje = document.querySelector(".alert");
  if (mensaje) {
    let tiempo = mensaje.classList.contains("alert-danger") ? 5000 : 4000;
    setTimeout(() => {
      mensaje.style.display = "none";
    }, tiempo);
  }

  // Manejar la navegación del menú de configuración
  const menuItems = document.querySelectorAll(".settings-item");
  const sections = document.querySelectorAll(".settings-section");

  menuItems.forEach((item) => {
    item.addEventListener("click", () => {
      menuItems.forEach((i) => i.classList.remove("active"));
      sections.forEach((section) => {
        section.classList.remove("active");
        section.classList.add("d-none");
      });

      item.classList.add("active");
      const sectionId = item.getAttribute("data-section");
      const targetSection = document.getElementById(sectionId);
      targetSection.classList.add("active");
      targetSection.classList.remove("d-none");
    });
  });

  // Configuración de Cropper.js
  let cropper;
  let croppedBlob = null;
  const profilePhotoInput = document.getElementById("profile-photo");
  const cropperImage = document.getElementById("cropper-image");
  const cropButton = document.getElementById("crop-button");
  const updatePhotoButton = document.getElementById("update-photo-button");
  const deletePhotoButton = document.getElementById("delete-photo-button");
  const fileName = document.getElementById("file-name");
  const photoMessage = document.getElementById("photo-message");
  const sidebarPhoto = document.getElementById("sidebar-photo");
  const sidebarIcon = document.getElementById("sidebar-icon");

  if (profilePhotoInput) {
    profilePhotoInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        // Verificar si ya hay una imagen de perfil
        if (
          document.getElementById("sidebar-photo") ||
          document.getElementById("delete-photo-button")
        ) {
          showMessage(
            "Por favor, elimina la imagen de perfil actual antes de subir una nueva.",
            "alert-danger"
          );
          profilePhotoInput.value = ""; // Limpiar input
          fileName.textContent = "Ningún archivo seleccionado";
          return;
        }
        fileName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) {
          cropperImage.src = e.target.result;
          document
            .querySelector(".cropper-container")
            .classList.remove("d-none");
          cropButton.classList.remove("d-none");

          if (cropper) {
            cropper.destroy();
          }
          cropper = new Cropper(cropperImage, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 0.8,
          });
        };
        reader.readAsDataURL(file);
      } else {
        fileName.textContent = "Ningún archivo seleccionado";
        document.querySelector(".cropper-container").classList.add("d-none");
        cropButton.classList.add("d-none");
        if (cropper) {
          cropper.destroy();
        }
      }
    });
  }

  if (cropButton) {
    cropButton.addEventListener("click", function () {
      if (cropper) {
        const canvas = cropper.getCroppedCanvas({
          width: 200,
          height: 200,
        });
        canvas.toBlob(function (blob) {
          croppedBlob = blob;
          cropper.destroy();
          document.querySelector(".cropper-container").classList.add("d-none");
          cropButton.classList.add("d-none");
        }, "image/jpeg");
      }
    });
  }

  if (updatePhotoButton) {
    updatePhotoButton.addEventListener("click", function () {
      const formData = new FormData();
      if (!croppedBlob) {
        showMessage(
          "Por favor, recorta la imagen antes de actualizar.",
          "alert-danger"
        );
        return;
      }
      formData.append("foto", croppedBlob, "cropped_profile.jpg");

      fetch("settings.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showMessage(data.message, "alert-success");
            // Actualizar sidebar
            if (sidebarIcon) {
              sidebarIcon.remove();
            }
            if (!sidebarPhoto) {
              const img = document.createElement("img");
              img.id = "sidebar-photo";
              img.alt = "Foto de perfil";
              img.className = "profile-icon";
              document.querySelector(".profile-icon").appendChild(img);
            }
            document.getElementById("sidebar-photo").src =
              data.photo_url + "?t=" + new Date().getTime();
            // Actualizar navbar
            const userMenuButton = document.getElementById("userMenu");
            userMenuButton.innerHTML = ""; // Limpiar todo el contenido
            const userMenuImg = document.createElement("img");
            userMenuImg.src = data.photo_url + "?t=" + new Date().getTime();
            userMenuImg.alt = "Foto de perfil";
            userMenuImg.style.width = "32px";
            userMenuImg.style.height = "32px";
            userMenuImg.style.borderRadius = "50%";
            userMenuImg.style.objectFit = "cover";
            userMenuButton.appendChild(userMenuImg);
            // Actualizar dropdown
            const dropdownLi = document.querySelector(
              ".dropdown-menu .d-flex.align-items-center"
            );
            const dropdownIcon = dropdownLi.querySelector("i");
            if (dropdownIcon) {
              dropdownIcon.remove();
            }
            const dropdownImg = document.createElement("img");
            dropdownImg.src = data.photo_url + "?t=" + new Date().getTime();
            dropdownImg.alt = "Foto de perfil";
            dropdownImg.style.width = "2rem";
            dropdownImg.style.height = "2rem";
            dropdownImg.style.borderRadius = "50%";
            dropdownImg.style.objectFit = "cover";
            dropdownImg.style.marginRight = "0.5rem";
            dropdownLi.insertBefore(
              dropdownImg,
              dropdownLi.querySelector("div")
            );
            // Limpiar formulario
            fileName.textContent = "Ningún archivo seleccionado";
            profilePhotoInput.value = "";
            croppedBlob = null;
            // Añadir botón de eliminar si no existe
            if (!document.getElementById("delete-photo-button")) {
              const deleteBtn = document.createElement("button");
              deleteBtn.type = "button";
              deleteBtn.className = "btn btn-danger";
              deleteBtn.id = "delete-photo-button";
              deleteBtn.textContent = "Eliminar foto";
              updatePhotoButton.parentElement.appendChild(deleteBtn);
              deleteBtn.addEventListener("click", deletePhoto);
            }
          } else {
            showMessage(
              data.error || "Error al actualizar la foto",
              "alert-danger"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showMessage("Ocurrió un error al actualizar la foto", "alert-danger");
        });
    });
  }

  function deletePhoto() {
    if (confirm("¿Estás seguro de eliminar tu foto de perfil?")) {
      const formData = new FormData();
      formData.append("action", "delete_photo");

      fetch("settings.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showMessage(data.message, "alert-success");
            // Actualizar sidebar
            const profileIconContainer =
              document.querySelector(".profile-icon");
            profileIconContainer.innerHTML = ""; // Limpiar contenido
            const icon = document.createElement("i");
            icon.id = "sidebar-icon";
            icon.className = "bi bi-person-circle";
            profileIconContainer.appendChild(icon);
            // Actualizar navbar
            const userMenuButton = document.getElementById("userMenu");
            userMenuButton.innerHTML = ""; // Limpiar contenido
            const userMenuIcon = document.createElement("i");
            userMenuIcon.className = "fa-solid fa-user";
            userMenuIcon.id = "icon-nav";
            userMenuButton.appendChild(userMenuIcon);
            // Actualizar dropdown
            const dropdownLi = document.querySelector(
              ".dropdown-menu .d-flex.align-items-center"
            );
            const dropdownImg = dropdownLi.querySelector("img");
            if (dropdownImg) {
              dropdownImg.remove();
            }
            const dropdownIcon = document.createElement("i");
            dropdownIcon.className = "fa-solid fa-user-circle fa-2x me-2";
            dropdownLi.insertBefore(
              dropdownIcon,
              dropdownLi.querySelector("div")
            );
            // Limpiar formulario
            document.getElementById("delete-photo-button").remove();
            fileName.textContent = "Ningún archivo seleccionado";
            profilePhotoInput.value = "";
            croppedBlob = null;
            document
              .querySelector(".cropper-container")
              .classList.add("d-none");
            cropButton.classList.add("d-none");
          } else {
            showMessage(
              data.error || "Error al eliminar la foto",
              "alert-danger"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showMessage("Ocurrió un error al eliminar la foto", "alert-danger");
        });
    }
  }

  if (deletePhotoButton) {
    deletePhotoButton.addEventListener("click", deletePhoto);
  }

  function showMessage(message, type) {
    photoMessage.innerHTML = `<div class="alert ${type} alert-sm">${message}</div>`;
    setTimeout(
      () => {
        photoMessage.innerHTML = "";
      },
      type === "alert-danger" ? 5000 : 4000
    );
  }
});

function confirmarCambios() {
  const form = document.getElementById("profileForm");
  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  const formData = new FormData(form);
  fetch("settings.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);
        window.location.reload();
      } else {
        alert(data.error || "Error al actualizar el perfil");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Ocurrió un error al actualizar el perfil");
    });
}

function actualizarContraseña() {
  const form = document.getElementById("securityForm");
  const newPassword = document.getElementById("new-password").value;
  const confirmPassword = document.getElementById("confirm-password");

  if (newPassword !== confirmPassword.value) {
    confirmPassword.classList.add("is-invalid");
    confirmPassword.classList.remove("is-valid");
    return;
  } else {
    confirmPassword.classList.remove("is-invalid");
    confirmPassword.classList.add("is-valid");
  }

  const lengthValid = newPassword.length >= 10;
  const upperValid = /[A-Z]/.test(newPassword);
  const numberValid = /[0-9]/.test(newPassword);
  const specialValid = /[@$!%*?&]/.test(newPassword);

  if (!lengthValid || !upperValid || !numberValid || !specialValid) {
    alert("La contraseña debe cumplir con todos los requisitos");
    return;
  }

  const formData = new FormData(form);
  formData.append("action", "update_password");

  fetch("settings.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);
        form.reset();
        confirmPassword.classList.remove("is-invalid");
        document.querySelectorAll(".password-requirements li").forEach((li) => {
          li.classList.remove("password-valid");
          li.classList.add("password-invalid");
        });
      } else {
        alert(data.error || "Error al actualizar la contraseña");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Ocurrió un error al actualizar la contraseña");
    });
}

// Tiempo máximo de inactividad en milisegundos (10 minutos = 600000 ms)
const tiempoMaximoInactividad = 600000;
let tiempoInactividad = 0;
let sesionCerrada = false;

function reiniciarTemporizador() {
  if (!sesionCerrada) {
    tiempoInactividad = 0;
  }
}

function verificarInactividad() {
  if (sesionCerrada) return;

  tiempoInactividad += 1000;
  if (tiempoInactividad >= tiempoMaximoInactividad) {
    fetch("index.php", { method: "POST" })
      .then((response) => {
        if (response.redirected) {
          sesionCerrada = true;
          alert("Sesión cerrada por inactividad.");
          window.location.href = response.url;
        }
      })
      .catch((error) => {
        console.error("Error al verificar la sesión:", error);
      });
  }
}

window.onload = reiniciarTemporizador;
window.onmousemove = reiniciarTemporizador;
window.onkeypress = reiniciarTemporizador;
window.onclick = reiniciarTemporizador;
window.onscroll = reiniciarTemporizador;

setInterval(verificarInactividad, 1000);
