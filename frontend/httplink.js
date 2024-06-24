import useAjax from "./@ajax.js";

export function httplinksInit(
  res = (data) => undefined,
  rej = (data) => undefined,
  allway = () => undefined,
  refreshOn = 0, // 0: Allway, 1: Success, -1: Failed
  dyn_data = ($lnk, $method) => ({})
) {
  document.querySelectorAll("a[http-method]").forEach((el) => {
    let $method = el.getAttribute("http-method") ?? "GET";
    let $lnk = el.getAttribute("href") ?? "./";

    el.addEventListener("click", (e) => {
      e.preventDefault();
      useAjax($lnk, dyn_data($lnk, $method), $method)
        .then((response) => {
          res(response) && refreshOn === 1 && location.reload();
        })
        .catch((response) => {
          rej(response) && refreshOn === -1 && location.reload();
        })
        .finally(() => {
          refreshOn === 0 && location.reload();
        });
    });
  });
}
