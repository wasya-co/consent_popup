(function ($) {
  Drupal.behaviors.consent_popup = {
    attach: function (context, settings) {
      const popupOptions = drupalSettings.consent_popup;
      const cookiename = popupOptions.cookie_name;
      if (getCookie(cookiename) !== "true") {
        let d = new Date();
        const days = popupOptions.cookie_life;
        const doc = document.documentElement;
        const toBlur = popupOptions.to_blur;
        const redirect = popupOptions.redirect;
        const declineText = popupOptions.text_decline;
        const non_blocking = popupOptions.non_blocking;
        const redirect_url = popupOptions.redirect_url;
        for (let i = 0; i < toBlur.length; i++) {
          let element = $(toBlur[i]);
          if (element.length) {
            element.addClass("blured-element");
          }
        }
        doc.style.setProperty(
          "--consent-popup-bg-color",
          popupOptions.bg_color
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
          const cookie_value = non_blocking ? "true" : "false";
          if (non_blocking) {
            document.cookie = `${cookiename}=${cookie_value}; expires=${expiration}; path=/`;
          }
          if (redirect) {
            // make sure the cookie is set
            setTimeout(function () {
              window.location.replace(redirect_url);
            }, 500);
          } else {
            $("#consent-popup .consent-text").html(
              "<h2>" + declineText + "</h2>"
            );
            $("#consent-popup .consent-buttons").remove();
            $("#consent-popup a").removeClass("visually-hidden");
            document.cookie = `${cookiename}=${cookie_value}; expires=${expiration}; path=/`;
          }
        });
      }

      function getCookie(cName) {
        const name = cName + "=";
        const cDecoded = decodeURIComponent(document.cookie);
        const cArr = cDecoded.split("; ");
        let res;
        cArr.forEach((val) => {
          if (val.indexOf(name) === 0) {
            res = val.substring(name.length);
          }
        });
        return res;
      }
    },
  };
})(jQuery);
