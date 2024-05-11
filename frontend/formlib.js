import bd from "https://cdn.jsdelivr.net/gh/parsa-mostafaie/betterdom@master/betterdom.js";

class FormSubmitController {
  $;
  waitTabs = [];
  constructor($el) {
    this.$ = $el;
  }
  async loadJQ() {
    await bd.ldScript(
      "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    );
    return 0;
  }
  async AjaxButton($btn) {
    await this.loadJQ();
    let $el = this.$;
    let fsc = this;
    const $ = jQuery;
    $btn.addEventListener("click", () => false);
    $($btn).click(function (evt) {
      evt.preventDefault();
      fsc.waitTabs.forEach((e) => e.classList.remove("d-none"));

      var button = $(evt.target);
      let df = new FormData(fsc.$);
      df.append(button.attr("name"), button.attr("value"));

      $.ajax({
        url: fsc.$.getAttribute("action"),
        type: "POST",
        data: df,
        success: function (data) {
          console.log(data);
          data = JSON.parse(data);
          if (data.header.redirect) {
            // data.redirect contains the string URL to redirect to
            window.location.href = data.header.redirect;
          }
          let body = data.body;
          let err = body.error;
          if (err) {
            alert("Error: " + err);
          }
        },
        error: function (data) {},
        complete: function (data) {
          fsc.waitTabs.forEach((e) => e.classList.add("d-none"));
        },
        cache: false,
        contentType: false,
        processData: false,
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

document.querySelectorAll("form[submit-control]").forEach((el) => {
  let obj = new FormSubmitController(el);
  let attr = (a) => el.getAttribute("form-" + a) || undefined;
  obj.SubmitWaitTab(attr("wait"));
  el.querySelectorAll('[ajax-submit][type="submit"]').forEach((aj) => {
    obj.AjaxButton(aj);
  });
});
