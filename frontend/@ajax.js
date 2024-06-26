import bd from "https://cdn.jsdelivr.net/gh/parsa-mostafaie/betterdom@master/betterdom.js";

let __jq__loaded = false;
export async function useJQuery() {
  if (__jq__loaded) return;
  __jq__loaded = true;
  await bd.ldScript("https://code.jquery.com/jquery-3.7.1.min.js");
  return 0;
}

export default function useAjax(url, data, method = "POST", $request = {}) {
  // await useJQuery();
  return new Promise((res, rej) => {
    fetch(url, {
      cache: "no-cache",
      method,
      body: data,
      credentials: "same-origin",
      ...$request,
    })
      .then((response) => {
        if (response.ok) {
          res(response);
        } else {
          rej(response);
        }
      })
      .catch(rej);
  });
  // return new Promise((res, rej) => {
  //   $.ajax({
  //     url: url,
  //     type: method,
  //     data: data,
  //     success: function (data) {
  //       res(data);
  //     },
  //     error: function (data) {
  //       rej(data);
  //     },
  //     cache: false,
  //     contentType: false,
  //     processData: false,
  //   });
  // });
}
