import useAjax from "./@ajax.js";

document.querySelectorAll("a[http-method]").forEach((el) => {
  let $method = el.getAttribute("http-method") ?? "GET";
  let $lnk = el.getAttribute("href") ?? "./";

  el.addEventListener("click", (e) => {
    e.preventDefault();
    useAjax($lnk, {}, $method).finally(() => {
      location.reload();
    });
  });
});
