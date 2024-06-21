import useAjax from "./@ajax.js";

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
    allway = () => undefined,
    submit = (data) => undefined,
    redirect = true
  ) {
    let fsc = this;

    $btn.addEventListener("click", function (evt) {
      evt.preventDefault();
      fsc.waitTabs.forEach((e) => e.classList.remove("d-none"));

      let df = new FormData(fsc.$);
      let button = evt.target;
      df.append(button.getAttribute("name"), button.getAttribute("value"));
      button.setAttribute("disabled", "disabled");
      submit(df);
      useAjax(
        fsc.$.getAttribute("form-action"),
        df,
        fsc.$.getAttribute("form-method") ?? "POST"
      )
        .then((res) => res.json())
        .then((json) => {
          if (json.header.redirect && redirect) {
            res(json);
            // data.redirect contains the string URL to redirect to
            window.location.href = json.header.redirect;
          }
          let body = json.body;
          let err = body.error;
          if (err) {
            rej({ json, err: err });
            return;
          }
          res({ json });
        })
        .finally(() => {
          fsc.waitTabs.forEach((e) => e.classList.add("d-none"));
          button.removeAttribute("disabled");
          allway();
        })
        .catch(rej);
    });
  }
  SubmitWaitTab($query) {
    if (!$query) return this;
    let $el = document.querySelector($query);
    $el.classList.add("d-none");
    window.addEventListener("load", () => $el.classList.add("d-none"));
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
