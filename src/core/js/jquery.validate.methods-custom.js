jQuery.validator.addMethod("alphanumericstrict", function(value, element) {
  return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
}, "Must contain only letters and digits");

jQuery.validator.addClassRules("alphanumericstrict", { alphanumericstrict : true });
