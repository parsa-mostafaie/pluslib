import useAjax from "./@ajax.js";

export function httplinksInit(
  res = (data) => undefined,
  rej = (data) => undefined,
  allway = () => undefined,
  $selector = "a[http-method]",
  refreshOn = window.httplinksConfig?.refreshOn ?? 0, // 0: Allway, 1: Success, -1: Failed
  dyn_data = (el) => ({}),
  $follow = true
) {
  document.querySelectorAll(`:is(${$selector}):not(hl-eh)`).forEach((el) => {
    let $method = el.getAttribute("http-method") ?? "GET";
    let $lnk = el.getAttribute("href") ?? "./";

    el.addEventListener("click", (e) => {
      e.preventDefault();
      e.pluslib_wait_fetch = true;
      let action = () => {
        useAjax($lnk, dyn_data(el), $method, {}, $follow)
          .then((response) => {
            res(response) && refreshOn === 1 && location.reload();
          })
          .catch((response) => {
            rej(response) && refreshOn === -1 && location.reload();
          })
          .finally(() => {
            allway() && refreshOn === 0 && location.reload();
            (e.pluslib_fetch_actions ?? []).forEach((action) => action());
          });
      };
      if (e.pluslib_wait) {
        e.pluslib_actions ??= [];
        e.pluslib_actions.push(action);
      } else {
        action();
      }
    });
    el.setAttribute("hl-eh", "set");
  });
}

window.httplinksInit = httplinksInit;

httplinksInit();
