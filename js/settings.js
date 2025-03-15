class nrgShippingSettings {
    constructor(options) {
        this.$wrapper = options.$wrapper;
        this.$submit_button = this.$wrapper.closest('form').find(':submit');
        this.html = options.html;
        this.urls = options.urls;
        import('./webasyst-error-parser.js').then(module => this.error_parser = module.default);
        this.init();
    }

    init() {
        this.#initTabs();
        this.#initInputs();
    }

    #initInputs() {
        this.#initZipInput();
    }

    #initTabs() {
        const $tabs = this.$wrapper.find('.js-tabs');
        $tabs.on('click', '>li>a', event => {
            event.preventDefault();
            event.stopPropagation();
            const $this = $(event.target),
                $this_li = $this.closest('li');
            if ($this_li.hasClass('selected')) {
                return false;
            }
            const $tabs_content_container = $tabs.next('.js-tabs-content-container'),
                selected_id = $this.data('tab');

            if (!selected_id) {
                return false;
            }

            const $tab_content = $(`#ws-plugin-nrg-${selected_id}-tab`);
            if (!$tab_content.length) {
                return false;
            }

            $tabs.find('.selected').removeClass('selected');
            $this_li.addClass('selected');
            $tabs_content_container.find('.js-tab-content:visible').addClass('hidden');
            $tab_content.removeClass('hidden');

            return false;
        });
    }

    #initZipInput() {
        const $zip_input = this.$wrapper.find('.js-nrg-sender-zip');
        $zip_input.attr('pattern', '[0-9]{6}');
        $zip_input
            .on('input', event => {
                if (!event.target.validity.valid && !event.target.classList.contains('state-error')) {
                    event.target.classList.add('state-error');
                } else if (event.target.validity.valid && event.target.classList.contains('state-error')) {
                    event.target.classList.remove('state-error');
                }
            })
            .on('change', event => {
                const $this = $(event.target),
                    $spinner = $(this.html.spinner);
                if (event.target.value.trim().length && event.target.validity.valid) {
                    this.$wrapper.find('.js-nrg-sender-city-code, .js-nrg-sender-city-name').val('');
                    $this.prop('readonly', true).after($spinner);
                    $this.siblings('.js-error-message').remove();
                    $.ajax(this.urls.search_city, {
                        global:   false,
                        dataType: 'json',
                        data:     {zip: event.target.value},
                        success:  response => {
                            if (response.status === 'ok') {
                                this.$wrapper.find('.js-nrg-sender-city-code').val(response.data.city.id);
                                this.$wrapper.find('.js-nrg-sender-city-name').val(response.data.city.name);
                            } else {
                                const errors = this.error_parser.parse(response);
                                $(this.html.state_error_hint.replace('%message%', errors[0])).insertAfter($this);
                            }
                        },
                        error:    jqXHR => {
                            const errors = this.error_parser.parse(jqXHR);
                            $(this.html.state_error_hint.replace('%message%', errors[0])).insertAfter($this);
                        }
                    }).always(() => {
                        $this.prop('readonly', false);
                        $spinner.remove();
                    });
                }
            })
    }
}
