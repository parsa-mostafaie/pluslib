//? Template
/*
<!-- One "tab" for each step in the form: -->
<div class="tab">Name:
  <p><input placeholder="First name..." ></p>
  <p><input placeholder="Last name..." ></p>
</div>

<div class="tab">Login Info:
  <p><input placeholder="Username..." ></p>
  <p><input placeholder="Password..."></p>
</div>

 */

class multiStepForm {
  $ = null;
  p = "Previous";
  n = "Next";
  s = "Submit";
  currentTab = 0; // Current tab is set to be the first tab (0)

  get prevBtn() {
    return this.$.querySelector("#prevBtn");
  }

  get ftab() {
    return this.$.querySelector(".final-tab");
  }
  get nextBtn() {
    return this.$.querySelector("#nextBtn");
  }
  get submitBtn() {
    return this.$.querySelector("#submitBtn");
  }

  get indic() {
    return this.$.querySelector(".indic");
  }

  get tabs() {
    return [...this.$.querySelectorAll(".tab")];
  }

  get currentTabEl() {
    return this.tabs[this.currentTab];
  }

  get steps() {
    return this.$.getElementsByClassName("step");
  }

  constructor($elem, p = "Previous", n = "Next", s = "Submit") {
    this.p = p;
    this.n = n;
    this.s = s;
    this.$ = $elem;
    this.currentTab = 0;
    this.initControls();
    this.showTab(this.currentTab); // Display the current tab
  }

  initControls() {
    let tabCount = this.tabs.length;
    let html = `
<!-- Circles which indicates the steps of the form: -->
<div style="text-align:center;margin-top:40px;">${new Array(tabCount)
      .fill(
        `
  <span class="step"></span>`
      )
      .join("")}
</div>`;
    this.indic.insertAdjacentHTML("beforeend", html);
    this.prevBtn.addEventListener("click", () => this.nextPrev(-1));
    this.nextBtn.addEventListener("click", () => this.nextPrev(1));
    this.nextBtn.innerText = this.n;
    this.prevBtn.innerText = this.p;
    this.submitBtn.innerText = this.s;
  }
  showTab(n) {
    // This function will display the specified tab of the form ...
    var x = this.tabs;
    x[n].classList.add("d-inline");
    x[n].classList.remove("d-none");
    // ... and fix the Previous/Next buttons:
    if (n == 0) {
      this.prevBtn.classList.add("d-none");
      this.prevBtn.classList.remove("d-inline");
    } else {
      this.prevBtn.classList.remove("d-none");
      this.prevBtn.classList.add("d-inline");
    }
    if (n == x.length - 1) {
      this.nextBtn.classList.add("d-none");
      this.nextBtn.classList.remove("d-inline");

      this.submitBtn.classList.add("d-inline");
      this.submitBtn.classList.remove("d-none");
    } else {
      this.nextBtn.classList.remove("d-none");
      this.nextBtn.classList.add("d-inline");

      this.submitBtn.classList.add("d-none");
      this.submitBtn.classList.remove("d-inline");
    }
    // ... and run a function that displays the correct step indicator:
    this.fixStepIndicator(n);
  }

  nextPrev(n) {
    // This function will figure out which tab to display
    var x = this.tabs;
    // Exit the function if any field in the current tab is invalid:
    if (n == 1 && !this.validateForm()) return false;
    // Hide the current tab:
    this.currentTabEl.classList.add("d-none");
    // Increase or decrease the current tab by 1:
    this.currentTab = this.currentTab + n;
    // Otherwise, display the correct tab:
    this.showTab(this.currentTab);
  }

  validateForm() {
    // This function deals with validation of the form fields
    var x,
      y,
      i,
      valid = true;
    x = this.tabs;
    y = this.currentTabEl.getElementsByTagName("input");
    // A loop that checks every input field in the current tab:
    for (i = 0; i < y.length; i++) {
      // If a field is empty...
      if (y[i].value == "") {
        // add an "invalid" class to the field:
        y[i].classList.add("invalid");
        // and set the current valid status to false:
        valid = false;
      }
    }
    // If the valid status is true, mark the step as finished and valid:
    if (valid) {
      this.steps[this.currentTab].classList.add("finish");
    }
    return valid; // return the valid status
  }

  fixStepIndicator(n) {
    // This function removes the "active" class of all steps...
    var i,
      x = this.steps;
    for (i = 0; i < x.length; i++) {
      x[i].classList.remove("active");
    }
    //... and adds the "active" class to the current step:
    x[n].classList.add("active");
  }
}

window.addEventListener("load", () =>
  document.querySelectorAll(".ms-form").forEach((el) => {
    let attr = (y) => el.getAttribute("data-form-" + y) || undefined;
    let msfo = new multiStepForm(el, attr("p"), attr("n"), attr("s"));
    let ct = el.getElementsByClassName("tab")[msfo.currentTab];
    let x = ct.getElementsByTagName("input");
    [...x].forEach((a) => {
      a.addEventListener("input", () => a.classList.remove("invalid"));
    });
  })
);
