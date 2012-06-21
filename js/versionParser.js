(function(){
    YAHOO.scmfviz.versionParser = function() {
        var errors = [];
        var version_numbers = [];
        function addError(error) {
            errors[errors.length] = error;
        }
        function checkLength() {
            if (version_numbers.length <= 2) {
                addError("Not enough version nubmer compounds. It should consist of at least 3 symbols separated by '.' symbols");
            }
        }
        function isInteger(s) {
            return (s.toString().search(/^-?[0-9]+$/) == 0);
        }
        function checkSymbols() {
            for(var i = 0; i < version_numbers.length; i++) {
                if (version_numbers[i] != 'x' && !isInteger(version_numbers[i])) {
                    addError("Version compound " + version_numbers[i] + " is not an integer or 'x' symbol");
                }
            }
        }
        return {
            isValid: function (version) {
                version_numbers = version.split('.');
                checkLength();
                checkSymbols();
                return (errors.length == 0);
            },
            getErrors: function () {
                return errors;
            }
        };
    }
})();