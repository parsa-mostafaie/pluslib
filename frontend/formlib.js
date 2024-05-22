import useAjax, { useJQuery } from "./@ajax.js";

class FormSubmitController {
  $;
  waitTabs = [];
  constructor($el, $q = false) {
    this.$ = !$q ? $el : document.querySelector($el);
  }
  async AjaxButton(
    $btn,
    res = (data) => undefined,
    rej = (data) => undefined,
    allway = (data) => undefined,
    submit = (data) => undefined,
    redirect = true
  ) {
    await useJQuery();
    let fsc = this;
    const $ = jQuery;
    $btn.addEventListener("click", () => false);

    $($btn).click(function (evt) {
      evt.preventDefault();
      fsc.waitTabs.forEach((e) => e.classList.remove("d-none"));

      var button = $(evt.target);
      let df = new FormData(fsc.$);
      df.append(button.attr("name"), button.attr("value"));
      submit(df);
      useAjax(fsc.$.getAttribute("action"), df)
        .then((data) => {
          let jdata = JSON.parse(data);
          if (jdata.header.redirect && redirect) {
            // data.redirect contains the string URL to redirect to
            window.location.href = jdata.header.redirect;
          }
          let body = jdata.body;
          let err = body.error;
          if (err) {
            rej({ data: data, jdata: jdata, err: err });
            return;
          }
          res({ data, jdata });
        })
        .catch(rej)
        .finally((data) => {
          fsc.waitTabs.forEach((e) => e.classList.add("d-none"));
          allway(data);
        });
      return false;
    });
  }
  SubmitWaitTab($query) {
    if (!$query) return this;
    document.querySelector($query).classList.add("d-none");
    this.waitTabs.push(document.querySelector($query));
    return this;
  }
}

window.FormLibInitializer = {
  settings: {},
  findLast(el) {
    let res = [];
    for (let [k, v] of Object.entries(this.settings)) {
      if (el.matches(k)) {
        res = v;
      }
    }
    return res;
  },
  setting(q, ...attr) {
    this.settings[q] = attr;
    return this;
  },
  init() {
    document.querySelectorAll("form[submit-control]").forEach((el) => {
      let obj = new FormSubmitController(el);
      let attr = (a) => el.getAttribute("form-" + a) || undefined;
      obj.SubmitWaitTab(attr("wait"));
      el.querySelectorAll('[ajax-submit][type="submit"]').forEach((aj) => {
        obj.AjaxButton(aj, ...this.findLast(aj));
      });
    });
  },
};
