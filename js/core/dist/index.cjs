"use strict";
var __typeError = (msg) => {
  throw TypeError(msg);
};
var __accessCheck = (obj, member, msg) => member.has(obj) || __typeError("Cannot " + msg);
var __privateGet = (obj, member, getter) => (__accessCheck(obj, member, "read from private field"), getter ? getter.call(obj) : member.get(obj));
var __privateAdd = (obj, member, value) => member.has(obj) ? __typeError("Cannot add the same private member more than once") : member instanceof WeakSet ? member.add(obj) : member.set(obj, value);
var __privateSet = (obj, member, value, setter) => (__accessCheck(obj, member, "write to private field"), setter ? setter.call(obj, value) : member.set(obj, value), value);

// src/lang/language.ts
var Language = class {
  /**
   * Отдает текст языкового ключа
   *
   * @param {string} name
   * @returns {string}
   */
  get(name) {
    let message = "";
    try {
      message = this.has(name) ? BX.Loc.getMessage(name) : "";
    } catch {
    }
    return message;
  }
  /**
   * Проверка наличия языкового ключа
   *
   * @param {string} name
   * @returns {boolean}
   */
  has(name) {
    return BX.Loc.hasMessage(name);
  }
};

// src/index.ts
var _extensions, _lang, _a;
window.FrontBoot = new (_a = class {
  constructor() {
    __privateAdd(this, _extensions);
    __privateAdd(this, _lang);
    __privateSet(this, _extensions, /* @__PURE__ */ new Map());
    __privateSet(this, _lang, new Language());
  }
  getMessage(name) {
    return this.lang.get(name);
  }
  get lang() {
    return __privateGet(this, _lang);
  }
  get extensions() {
    return __privateGet(this, _extensions);
  }
}, _extensions = new WeakMap(), _lang = new WeakMap(), _a)();
//# sourceMappingURL=index.cjs.map