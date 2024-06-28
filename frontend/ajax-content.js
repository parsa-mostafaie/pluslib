import useAjax from "./@ajax.js";

export function ajaxContentLoad(
  $selector = "[ajax-container]",
  res = (data) => undefined,
  rej = (data) => undefined,
  allway = () => undefined,
  dyn_data = ($content) => ({}),
  $follow = true
) {
  document.querySelectorAll($selector).forEach((container) => {
    let $contents = container.querySelectorAll("[ajax-content]");

    ajaxContentReLoads(container, arguments);

    $contents.forEach(($content) => {
      let $lnk = $content.getAttribute("href") ?? "./";
      let $method = $content.getAttribute("http-method") ?? "GET";
      let $loading = container.querySelector($content.getAttribute("loading"));

      $loading.classList.remove("d-none");
      $content.innerHTML = "";

      function loaded(response) {
        $loading.classList.add("d-none");
        addJSX(response, $content);
      }

      useAjax($lnk, dyn_data($content), $method, {}, $follow)
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

window.addEventListener("load", () => ajaxContentLoad());

window.ajaxContentLoad = ajaxContentLoad;

function addJSX($html, $content) {
  let scrollTop = window.scrollY;
  $html = `<div>${$html}</div>`;
  let domp = new DOMParser();
  let doc = domp.parseFromString($html, "text/html");
  let $scripts = [];
  doc.querySelectorAll("script").forEach((scriptTag) => {
    let newS = document.createElement("script");
    newS.textContent = scriptTag.textContent;
    scriptTag.remove();
    $scripts.push(newS);
  });
  $content.append(...doc.firstChild.childNodes);
  document.body.append(...$scripts);
  $scripts.forEach((script) => script.remove());
  window.scrollY = scrollTop;
}

function ajaxContentReLoads(container = undefined, args = undefined) {
  let $arr = [...document.querySelectorAll("[ajax-reload]:not([ar-eh])")];
  if (container) {
    $arr.filter((v) => container.matches(v.getAttribute("ajax-reload")));
  }
  $arr.forEach((ar) => {
    ar.addEventListener("click", (ev) => {
      function action() {
        ajaxContentLoad(ar.getAttribute("ajax-reload"), ...(args ?? []));
      }
      if (ev.pluslib_wait) {
        ev.pluslib_actions ??= [];
        ev.pluslib_actions.push(action);
      } else {
        action();
      }
    });
    ar.setAttribute("ar-eh", "set");
  });
}

window.ajaxContentReLoads = ajaxContentReLoads;
