import useAjax from "./@ajax.js";

export function ajaxContentLoad(
  res = (data) => undefined,
  rej = (data) => undefined,
  allway = () => undefined,
  $selector = "[ajax-container]",
  dyn_data = ($lnk, $method) => ({})
) {
  document.querySelectorAll($selector).forEach((container) => {
    let $contents = container.querySelectorAll("[ajax-content]");

    $contents.forEach(($content) => {
      let $lnk = $content.getAttribute("href") ?? "./";
      let $method = $content.getAttribute("http-method") ?? "GET";
      let $loading = container.querySelector($content.getAttribute("loading"));

      $loading.classList.remove("d-none");

      function loaded(response) {
        $loading.classList.add("d-none");
        $content.innerHTML = response;
      }

      useAjax($lnk, dyn_data($lnk, $method), $method)
        .then((res) => {
          return res.text();
        })
        .then((response) => {
          loaded(response);
          res(response);
        })
        .catch(rej)
        .finally(allway);
    });
  });
}

window.addEventListener("load", ajaxContentLoad);
