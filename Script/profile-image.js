const input = document.getElementById("nova_foto");
const image = document.getElementById("foto-perfil");

input?.addEventListener("change", (event) => {
  const file = event.target.files?.[0];
  if (!file || !file.type.startsWith("image/") || !image) return;
  const previousUrl = image.dataset.previewUrl;
  if (previousUrl) URL.revokeObjectURL(previousUrl);
  const previewUrl = URL.createObjectURL(file);
  image.dataset.previewUrl = previewUrl;
  image.src = previewUrl;
});
