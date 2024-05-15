function ffqs() {
  document.querySelectorAll("[ffqs]").forEach((v) => {
    let $ffqs = v.getAttribute("ffqs");
    if ($ffqs) {
      let $usp = new URLSearchParams(window.location.search).get($ffqs);
      if (!$usp) return;
      if (v.tagName == "INPUT") {
        v.value = $usp;
      } else {
        v.textContent = $usp;
      }
    }
  });
}
