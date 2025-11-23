(function() {
    if (!window.FrontBoot) {
        window.FrontBoot = {
            getMessage(name) {
                let message = '';

                try {
                    message = BX.Loc.getMessage(name) ? BX.Loc.getMessage(name) : '';
                } catch {
                }

                return message;
            }
        };
    }
})()