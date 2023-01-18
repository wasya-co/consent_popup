(function ($) {
  Drupal.behaviors.consent_popup = {
    attach: function (context, settings) {
      const cookiename = drupalSettings.consent_popup.cookie_name;
      if (getCookie(cookiename) !== "true") {
        let d = new Date();
        const days = drupalSettings.consent_popup.cookie_life;
        const declineText = drupalSettings.consent_popup.text_decline;
        const doc = document.documentElement;
        const toBlur = drupalSettings.consent_popup.to_blur;
        for (let i = 0; i < toBlur.length; i++) {
          let element = $(toBlur[i]);
          if (element.length) {
            element.addClass("blured-element");
          }
        }
        doc.style.setProperty(
          "--consent-popup-bg-color",
          drupalSettings.consent_popup.bg_color
        );
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        const expiration = d.toUTCString();
        $("body").addClass("consent-popup-opened");
        $("#consent-popup .accept", context).on("click", function () {
          $("body").removeClass("consent-popup-opened");
          document.cookie =
            cookiename + "=true; expires=" + expiration + "; path=/";
        });
        $("#consent-popup .decline", context).on("click", function () {
          $("#consent-popup .consent-text").html(
            "<h2>" + declineText + "</h2>"
          );
          $("#consent-popup .consent-buttons").remove();
          $("#consent-popup a").removeClass("visually-hidden");
          document.cookie =
            cookiename + "=false; expires=" + expiration + "; path=/";
        });
      }

      function getCookie(cName) {
        const name = cName + "=";
        const cDecoded = decodeURIComponent(document.cookie);
        const cArr = cDecoded.split("; ");
        let res;
        cArr.forEach((val) => {
          if (val.indexOf(name) === 0) res = val.substring(name.length);
        });
        return res;
      }
    },
  };
})(jQuery);
