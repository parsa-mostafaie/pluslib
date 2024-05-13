import bd from "https://cdn.jsdelivr.net/gh/parsa-mostafaie/betterdom@master/betterdom.js";

let __jq__loaded = false;
export async function useJQuery() {
  if (__jq__loaded) return;
  await bd.ldScript(
    "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"
  );
  return 0;
}

export default async function useAjax(url, data) {
  await useJQuery();
  return new Promise((res, rej) => {
    $.ajax({
      url: url,
      type: "POST",
      data: data,
      success: function (data) {
        res(data);
      },
      error: function (data) {
        rej(data);
      },
      complete: function (data) {
        res(data);
      },
      cache: false,
      contentType: false,
      processData: false,
    });
  });
}
