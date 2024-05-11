function createDynamicApp(name, vars, temp, root) {
  this.name = name;
  this.vars = vars;
  this.iterators = {};

  this.comp = (val) => {
    let f = new Function(...Object.keys(this.vars), val);
    return f.call({ iterators: this.iterators }, [Object.values(this.vars)]);
  };
  this.render = function (template) {
    const renderAapp = (app) => {
      let dynamics = app.querySelectorAll(`dynamic`);
      let all = app.querySelectorAll(`*:not(dynamic, loop)`);
      let loops = app.querySelectorAll("loop");
      dynamics.forEach((d) => {
        let val = d.getAttribute("val");
        repWithHTML(d, this.comp(val));
      });
      loops.forEach((l) => {
        let t = l.querySelector("template");
        let f = document.createDocumentFragment();
        let iterable = this.comp(l.getAttribute("iterable"));
        let iterator = l.getAttribute("iterator");

        if (!(iterator in this.iterators)) {
          [...iterable].forEach((value, index) => {
            this.iterators[iterator] = { value, index };
            let c = this.render(t);
            f.appendChild(c);
            console.log(t);
          }, this);
        } else {
          throw "iterator " + iterator + " exist!";
        }

        repWithFrag(l, f);
        delete this.iterators[iterator];
      });
      all.forEach((b) => {
        let p_binds = [...b.attributes].filter((a) => a.name.startsWith(":"));

        p_binds.forEach((a) => {
          b.setAttribute(a.name.slice(1), this.comp(a.value));
          b.removeAttribute(a.name);
        });
      });
    };
    if (template) {
      let clone = cloneTemplate(template);
      renderAapp(clone);
      return clone;
    }
  };
  const R = () => {
    if (temp && root) {
      temp = document.querySelector(temp);
      root = document.querySelector(root);
      root.innerHTML = "";
      root.append(this.render(temp));
    } else {
      throw "TEMP or ROOT is unset";
    }
  };
  R();
  this.set = (n, v) => {
    this.vars[n] = v;
    R();
    return this.vars[n];
  };
}

function cloneTemplate(template) {
  console.log(template.content);
  return template.content.cloneNode(true);
}

function repWithHTML(a, b) {
  typeof a == "string"
    ? (a.insertAdjacentHTML("afterend", b), a.remove())
    : a.replaceWith(b);
}

function repWithFrag(a, f) {
  a.replaceWith(f);
}
