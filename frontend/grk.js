function grk(e) {
  e.preventDefault();

  fetch(
    "/api/grk.php?url=" +
      e.target.getAttribute("data-href") +
      "&back=" +
      window.location.href
  )
    .then((res) => res.text())
    .then((res) => (window.location.href = res));
}

function data_href_init() {
  document.querySelectorAll("[data-href]").forEach((el) => {
    el.addEventListener("click", grk);
  });
}
