function setUrlParams(url, pn, pv) {
  let URLO = new URL(url || window.location.href);
  URLO.searchParams.set(pn, pv);
  return URLO.href;
}

/* {
    'attribute': {
        attrName: fn as (attrVal, curl) => newURL,
    }
} */
function anchors(selector, { attribute }) {
  [...document.querySelectorAll(selector)].forEach((el) => {
    let handle = function () {
      let curl = window.location.href;
      for (attrName in attribute) {
        let fn = attribute[attrName];
        let newURL = fn(el.getAttribute(attrName), curl);
        curl = newURL;
      }
      return curl;
    };
    el.href = handle();
  });
}
