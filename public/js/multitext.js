; (function (window, $, undefined) {

    var RESET_CLASS = 'reset-style';

    $.fn.multiTextToggleCollapse = function (settings) {
        var defalutConfig = {
            line: 1,
        };

        var config = $.extend(defalutConfig, settings);

        var $wrapBox = this;
        var wrapBox = $wrapBox[0];
        var text = $wrapBox.text();
        var textHeight = parseInt($wrapBox.css("height"));

        var lineHeightStr = $wrapBox.css("line-height"); 
        var fontSize = parseInt($wrapBox.css("font-size"));

        var strategyMode = {
            "normal": function () {
                return fontSize;
            },
            "px": function (num) {
                return +num;
            },
            "em": function (num) {
                return num * fontSize;
            },
            "%": function (num) {
                return num / 100 * fontSize;
            },
            "void": function (num) {
                return num * fontSize;
            }
        };

        var num = (lineHeightStr.match(/\d+(.\d+)?/) || [1])[0]; 
        var unit = lineHeightStr.replace(num, '') || 'void'; 
        var lineHeight = strategyMode[unit](num).toFixed(2);

        $wrapBox.addClass('tool-multiwrapbox');

        var visibleHeight = config.line * lineHeight;

        visibleHeight = Math.ceil(visibleHeight);

        if (textHeight <= visibleHeight) {
            return;
        }

        wrapBox.style.cssText = ';height:' + visibleHeight + 'px;';

        var textBox = document.createElement("div");

        textBox.className = "multi-text";
        textBox.innerHTML = text;

        var collapseBtn = document.createElement("span");

        collapseBtn.className = "after-op op-btn op-collapse";
        collapseBtn.innerHTML = "Read Less";

        textBox.appendChild(collapseBtn);

        var dots = document.createElement("span");

        dots.className = "after-dots";
        dots.innerHTML = "...";

        var expandBtn = document.createElement("span");

        expandBtn.className = "after-op op-btn";
        expandBtn.innerHTML = "More";
        var btnWrapBox = document.createElement("div");

        btnWrapBox.className = "multi-text_after";
        btnWrapBox.style.cssText = ';top:' + (config.line - 1) * lineHeight + 'px;';

        btnWrapBox.appendChild(dots);
        btnWrapBox.appendChild(expandBtn);

        $wrapBox.html('');
        $wrapBox.append(textBox);
        $wrapBox.append(btnWrapBox);


        var $btnWrapBox = $(btnWrapBox);

        expandBtn.onclick = function () {
            $btnWrapBox.hide();
            $wrapBox.addClass(RESET_CLASS);
        };

        collapseBtn.onclick = function () {
            $btnWrapBox.show();
            $wrapBox.removeClass(RESET_CLASS);
        };
    };

}(this, jQuery));
