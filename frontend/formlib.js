import bd from "https://cdn.jsdelivr.net/gh/parsa-mostafaie/betterdom@master/betterdom.js";

class FormSubmitController {
  $;
  waitTabs = [];
  ajax = null;
  constructor($el) {
    bd.ldScript(
      "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    );
    this.$ = $el;
    let fsc = this;
    $el.addEventListener("submit", (event) => {
      fsc.waitTabs.forEach((e) => e.classList.remove("d-none"));
      if (fsc.ajax) {
        event.preventDefault();
        var formData = new FormData(fsc.$);

        $.ajax({
          url: this.$.getAttribute("action"),
          type: "POST",
          data: formData,
          success: function (data) {
            fsc.waitTabs.forEach((e) => e.classList.add("d-none"));
          },
          cache: false,
          contentType: false,
          processData: false,
        });
      }
    });
  }
  SubmitWaitTab($query) {
    if (!$query) return;
    document.querySelector($query).classList.add("d-none");
    this.waitTabs.push(document.querySelector($query));
    return this;
  }
  _ajax($ajax) {
    if (!$ajax) return;
    this.ajax = true;
  }
}

document.querySelectorAll("form[submit-control]").forEach((el) => {
  let obj = new FormSubmitController(el);
  let attr = (a) => el.getAttribute("form-" + a) || undefined;
  let _ajax = el.hasAttribute("form-ajax");
  obj.SubmitWaitTab(attr("wait"))._ajax(_ajax);
});
