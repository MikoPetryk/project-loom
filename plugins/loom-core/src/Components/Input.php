<?php
/**
 * Input Components
 *
 * TextField, Checkbox, Switch, Slider - form input elements.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

use Loom\Core\Tokens\Colors;

// ════════════════════════════════════════════════════════════════════════════
// TEXT FIELD - Text input
// ════════════════════════════════════════════════════════════════════════════

class TextField extends Component {

    public function __construct(
        private ?string $value = null,
        private ?string $label = null,
        private ?string $placeholder = null,
        private ?string $name = null,
        private ?string $id = null,
        private string $type = 'text',
        private bool $required = false,
        private bool $disabled = false,
        private ?string $error = null,
        private ?string $helper = null,
        private ?string $onChange = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        // Auto-generate ID for accessibility if not provided
        $fieldId = $this->id ?? self::generateId('textfield');
        $helperId = $fieldId . '-helper';

        $wrapperMod = ($this->modifier ?? Modifier::new())
            ->flex()
            ->flexDirection('column')
            ->gap(4);

        $content = '';

        // Label with proper 'for' attribute
        if ($this->label) {
            $labelMod = Modifier::new()
                ->fontSize(14)
                ->fontWeight(500)
                ->color(Colors::text());
            $labelContent = esc_html($this->label);
            if ($this->required) {
                $labelContent .= $this->tag('span', ' *', Modifier::new()->color(Colors::error()));
            }
            $content .= $this->tag('label', $labelContent, $labelMod, ['for' => $fieldId]);
        }

        // Input with ID and aria attributes
        $inputMod = Modifier::new()
            ->padding(horizontal: 12, vertical: 10)
            ->rounded(8)
            ->fontSize(16)
            ->border($this->error
                ? '2px solid ' . Colors::error()
                : '1px solid ' . Colors::border()
            )
            ->style('outline', 'none')
            ->transition('border-color 0.2s ease');

        if ($this->disabled) {
            $inputMod->background(Colors::background())
                ->cursor('not-allowed')
                ->opacity(0.7);
        }

        $inputAttrs = [
            'type' => $this->type,
            'id' => $fieldId,
        ];
        if ($this->value !== null) {
            $inputAttrs['value'] = $this->value;
        }
        if ($this->name) {
            $inputAttrs['name'] = $this->name;
        }
        if ($this->placeholder) {
            $inputAttrs['placeholder'] = $this->placeholder;
        }
        if ($this->required) {
            $inputAttrs['required'] = true;
            $inputAttrs['aria-required'] = 'true';
        }
        if ($this->disabled) {
            $inputAttrs['disabled'] = true;
        }
        if ($this->onChange) {
            $inputAttrs['onchange'] = $this->onChange;
        }
        // Link to helper/error text for screen readers
        if ($this->error || $this->helper) {
            $inputAttrs['aria-describedby'] = $helperId;
        }
        if ($this->error) {
            $inputAttrs['aria-invalid'] = 'true';
        }

        $content .= $this->tag('input', '', $inputMod, $inputAttrs);

        // Helper/Error text with ID for aria-describedby
        if ($this->error) {
            $errorMod = Modifier::new()->fontSize(12)->color(Colors::error());
            $content .= $this->tag('span', esc_html($this->error), $errorMod, ['id' => $helperId, 'role' => 'alert']);
        } elseif ($this->helper) {
            $helperMod = Modifier::new()->fontSize(12)->color(Colors::textSecondary());
            $content .= $this->tag('span', esc_html($this->helper), $helperMod, ['id' => $helperId]);
        }

        return $this->tag('div', $content, $wrapperMod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// TEXT AREA - Multiline text input
// ════════════════════════════════════════════════════════════════════════════

class TextArea extends Component {

    public function __construct(
        private ?string $value = null,
        private ?string $label = null,
        private ?string $placeholder = null,
        private ?string $name = null,
        private ?string $id = null,
        private int $rows = 4,
        private bool $required = false,
        private bool $disabled = false,
        private ?string $error = null,
        private ?string $helper = null,
        private ?string $onChange = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        // Auto-generate ID for accessibility if not provided
        $fieldId = $this->id ?? self::generateId('textarea');
        $helperId = $fieldId . '-helper';

        $wrapperMod = ($this->modifier ?? Modifier::new())
            ->flex()
            ->flexDirection('column')
            ->gap(4);

        $content = '';

        // Label with proper 'for' attribute
        if ($this->label) {
            $labelMod = Modifier::new()
                ->fontSize(14)
                ->fontWeight(500)
                ->color(Colors::text());
            $labelContent = esc_html($this->label);
            if ($this->required) {
                $labelContent .= $this->tag('span', ' *', Modifier::new()->color(Colors::error()));
            }
            $content .= $this->tag('label', $labelContent, $labelMod, ['for' => $fieldId]);
        }

        // Textarea with ID and aria attributes
        $textareaMod = Modifier::new()
            ->padding(12)
            ->rounded(8)
            ->fontSize(16)
            ->border($this->error
                ? '2px solid ' . Colors::error()
                : '1px solid ' . Colors::border()
            )
            ->style('outline', 'none')
            ->style('resize', 'vertical')
            ->style('font-family', 'inherit');

        if ($this->disabled) {
            $textareaMod->background(Colors::background())->opacity(0.7);
        }

        $attrs = [
            'id' => $fieldId,
            'rows' => (string) $this->rows,
        ];
        if ($this->name) $attrs['name'] = $this->name;
        if ($this->placeholder) $attrs['placeholder'] = $this->placeholder;
        if ($this->required) {
            $attrs['required'] = true;
            $attrs['aria-required'] = 'true';
        }
        if ($this->disabled) $attrs['disabled'] = true;
        if ($this->onChange) $attrs['onchange'] = $this->onChange;
        // Link to helper/error text for screen readers
        if ($this->error || $this->helper) {
            $attrs['aria-describedby'] = $helperId;
        }
        if ($this->error) {
            $attrs['aria-invalid'] = 'true';
        }

        $content .= $this->tag('textarea', esc_html($this->value ?? ''), $textareaMod, $attrs);

        // Helper/Error text with ID for aria-describedby
        if ($this->error) {
            $errorMod = Modifier::new()->fontSize(12)->color(Colors::error());
            $content .= $this->tag('span', esc_html($this->error), $errorMod, ['id' => $helperId, 'role' => 'alert']);
        } elseif ($this->helper) {
            $helperMod = Modifier::new()->fontSize(12)->color(Colors::textSecondary());
            $content .= $this->tag('span', esc_html($this->helper), $helperMod, ['id' => $helperId]);
        }

        return $this->tag('div', $content, $wrapperMod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// CHECKBOX - Toggle selection
// ════════════════════════════════════════════════════════════════════════════

class Checkbox extends Component {

    public function __construct(
        private ?string $label = null,
        private bool $checked = false,
        private ?string $name = null,
        private ?string $value = null,
        private bool $disabled = false,
        private ?string $onChange = null,
        private ?string $color = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::primary();
    }

    public function render(): string {
        $wrapperMod = ($this->modifier ?? Modifier::new())
            ->style('display', 'inline-flex')
            ->alignItems('center')
            ->gap(8)
            ->cursor($this->disabled ? 'not-allowed' : 'pointer');

        if ($this->disabled) {
            $wrapperMod->opacity(0.5);
        }

        // Hidden input
        $inputAttrs = ['type' => 'checkbox'];
        if ($this->name) $inputAttrs['name'] = $this->name;
        if ($this->value) $inputAttrs['value'] = $this->value;
        if ($this->checked) $inputAttrs['checked'] = true;
        if ($this->disabled) $inputAttrs['disabled'] = true;
        if ($this->onChange) $inputAttrs['onchange'] = $this->onChange;

        $inputMod = Modifier::new()
            ->position('absolute')
            ->opacity(0)
            ->size(0);

        $input = $this->tag('input', '', $inputMod, $inputAttrs);

        // Custom checkbox visual
        $borderColor = $this->checked ? $this->color : Colors::border();
        $checkMod = Modifier::new()
            ->size(20)
            ->rounded(4)
            ->border("2px solid {$borderColor}")
            ->background($this->checked ? $this->color : 'transparent')
            ->flex()
            ->alignItems('center')
            ->justifyContent('center')
            ->transition('all 0.2s ease');

        $onPrimaryColor = Colors::onPrimary();
        $checkIcon = $this->checked
            ? '<svg width="14" height="14" fill="' . $onPrimaryColor . '" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>'
            : '';
        $check = $this->tag('span', $checkIcon, $checkMod);

        // Label
        $labelContent = $this->label ? esc_html($this->label) : '';

        $content = $input . $check . ($labelContent ? $this->tag('span', $labelContent) : '');

        return $this->tag('label', $content, $wrapperMod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// SWITCH - On/off toggle
// ════════════════════════════════════════════════════════════════════════════

class Switch_ extends Component {

    private static bool $styleInjected = false;

    public function __construct(
        private ?string $label = null,
        private bool $checked = false,
        private ?string $name = null,
        private ?string $id = null,
        private bool $disabled = false,
        private ?string $onChange = null,
        private ?string $color = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::primary();
    }

    private function injectStyles(): string {
        if (self::$styleInjected) {
            return '';
        }
        self::$styleInjected = true;

        return '<style>
.loom-switch { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.loom-switch--disabled { opacity: 0.5; cursor: not-allowed; }
.loom-switch__input { position: absolute; opacity: 0; width: 0; height: 0; }
.loom-switch__track { position: relative; width: 44px; height: 24px; border-radius: 12px; background: var(--loom-border, #e2e8f0); transition: background 0.2s ease; }
.loom-switch__thumb { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; border-radius: 50%; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.2); transition: left 0.2s ease, transform 0.2s ease; }
.loom-switch__input:checked + .loom-switch__track { background: var(--loom-switch-color, var(--loom-primary, #6366f1)); }
.loom-switch__input:checked + .loom-switch__track .loom-switch__thumb { left: 22px; }
.loom-switch__input:focus + .loom-switch__track { box-shadow: 0 0 0 2px var(--loom-switch-color, var(--loom-primary, #6366f1))33; }
.loom-switch:not(.loom-switch--disabled):hover .loom-switch__track { filter: brightness(0.95); }
.loom-switch:not(.loom-switch--disabled):active .loom-switch__thumb { transform: scale(0.9); }
</style>';
    }

    public function render(): string {
        $styles = $this->injectStyles();

        // Auto-generate ID for accessibility if not provided
        $switchId = $this->id ?? self::generateId('switch');
        $labelId = $switchId . '-label';

        $wrapperClasses = 'loom-switch';
        if ($this->disabled) {
            $wrapperClasses .= ' loom-switch--disabled';
        }

        $wrapperMod = ($this->modifier ?? Modifier::new())
            ->class($wrapperClasses);

        // Custom color via CSS variable
        if ($this->color !== Colors::primary()) {
            $wrapperMod->style('--loom-switch-color', $this->color);
        }

        // Hidden input with proper accessibility
        $inputAttrs = [
            'type' => 'checkbox',
            'class' => 'loom-switch__input',
            'id' => $switchId,
            'role' => 'switch',
            'aria-checked' => $this->checked ? 'true' : 'false',
        ];
        if ($this->name) $inputAttrs['name'] = $this->name;
        if ($this->checked) $inputAttrs['checked'] = true;
        if ($this->disabled) $inputAttrs['disabled'] = true;

        // Accessible label - use aria-labelledby if visible label exists, otherwise aria-label
        if ($this->label) {
            $inputAttrs['aria-labelledby'] = $labelId;
        } else {
            // Provide a default aria-label if no visible label
            $inputAttrs['aria-label'] = 'Toggle switch';
        }

        if ($this->onChange) {
            // Also update aria-checked when toggled
            $inputAttrs['onchange'] = "this.setAttribute('aria-checked', this.checked); " . $this->onChange;
        } else {
            $inputAttrs['onchange'] = "this.setAttribute('aria-checked', this.checked)";
        }

        $input = $this->tag('input', '', null, $inputAttrs);

        // Track with thumb inside
        $thumb = '<span class="loom-switch__thumb"></span>';
        $track = '<span class="loom-switch__track">' . $thumb . '</span>';

        // Label text with ID for aria-labelledby
        $labelText = $this->label
            ? '<span id="' . esc_attr($labelId) . '" class="loom-switch__label">' . esc_html($this->label) . '</span>'
            : '';

        return $styles . $this->tag('label', $input . $track . $labelText, $wrapperMod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// SLIDER - Range input
// ════════════════════════════════════════════════════════════════════════════

class Slider extends Component {

    public function __construct(
        private int|float $value = 50,
        private int|float $min = 0,
        private int|float $max = 100,
        private int|float $step = 1,
        private ?string $label = null,
        private ?string $name = null,
        private ?string $id = null,
        private bool $disabled = false,
        private bool $showValue = false,
        private ?string $onChange = null,
        private ?string $color = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::primary();
    }

    public function render(): string {
        // Auto-generate ID for accessibility if not provided
        $fieldId = $this->id ?? self::generateId('slider');

        // If we have a label, wrap everything in a container
        if ($this->label || $this->showValue) {
            $wrapperMod = ($this->modifier ?? Modifier::new())
                ->flex()
                ->flexDirection('column')
                ->gap(4);

            $content = '';

            // Label row with optional value display
            $labelMod = Modifier::new()
                ->flex()
                ->justifyContent('space-between')
                ->alignItems('center');
            $labelContent = '';

            if ($this->label) {
                $textColor = Colors::text();
                $labelContent .= '<span style="font-size:14px;font-weight:500;color:' . $textColor . '">' . esc_html($this->label) . '</span>';
            }
            if ($this->showValue) {
                $secondaryColor = Colors::textSecondary();
                $labelContent .= '<span id="' . esc_attr($fieldId) . '-value" style="font-size:14px;color:' . $secondaryColor . '">' . esc_html((string) $this->value) . '</span>';
            }

            if ($labelContent) {
                $content .= $this->tag('label', $labelContent, $labelMod, ['for' => $fieldId]);
            }

            // Slider input
            $content .= $this->renderSliderInput($fieldId);

            return $this->tag('div', $content, $wrapperMod);
        }

        // No label - just render the slider with modifier
        return $this->renderSliderInput($fieldId, $this->modifier);
    }

    private function renderSliderInput(string $fieldId, ?Modifier $modifier = null): string {
        $mod = ($modifier ?? Modifier::new())
            ->fillMaxWidth()
            ->height(24)
            ->style('appearance', 'none')
            ->style('-webkit-appearance', 'none')
            ->background('transparent')
            ->cursor($this->disabled ? 'not-allowed' : 'pointer');

        $attrs = [
            'type' => 'range',
            'id' => $fieldId,
            'min' => (string) $this->min,
            'max' => (string) $this->max,
            'step' => (string) $this->step,
            'value' => (string) $this->value,
        ];
        if ($this->name) $attrs['name'] = $this->name;
        if ($this->disabled) $attrs['disabled'] = true;
        if ($this->label) $attrs['aria-label'] = $this->label;

        // Update value display and call custom handler
        $onInput = '';
        if ($this->showValue) {
            $onInput = "document.getElementById('" . esc_js($fieldId) . "-value').textContent=this.value;";
        }
        if ($this->onChange) {
            $onInput .= $this->onChange;
        }
        if ($onInput) {
            $attrs['oninput'] = $onInput;
        }

        // Add CSS class for styling
        $mod->class('loom-slider');

        return $this->tag('input', '', $mod, $attrs);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// SELECT - Custom dropdown selection (fully styleable)
// ════════════════════════════════════════════════════════════════════════════

class Select extends Component {

    private static bool $styleInjected = false;
    private static int $instanceCount = 0;

    public function __construct(
        private array $options,                  // ['value' => 'label'] or ['label1', 'label2']
        private ?string $value = null,
        private ?string $label = null,
        private ?string $placeholder = null,
        private ?string $name = null,
        private ?string $id = null,
        private bool $required = false,
        private bool $disabled = false,
        private ?string $error = null,
        private ?string $onChange = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    private function injectStyles(): string {
        if (self::$styleInjected) {
            return '';
        }
        self::$styleInjected = true;

        return '<style>
.loom-select { position: relative; display: flex; flex-direction: column; gap: 4px; }
.loom-select__label { font-size: 14px; font-weight: 500; color: var(--loom-text, #1a1a1a); }
.loom-select__label--required { color: var(--loom-error, #ef4444); }
.loom-select__trigger {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    width: 100%; padding: 10px 12px; font-size: 16px; font-family: inherit;
    color: var(--loom-text, #1a1a1a); background: var(--loom-surface, #ffffff);
    border: 1px solid var(--loom-border, #e2e8f0); border-radius: 8px;
    cursor: pointer; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    text-align: left;
}
.loom-select__trigger:hover:not(:disabled) { border-color: var(--loom-primary, #6366f1); }
.loom-select__trigger:focus { border-color: var(--loom-primary, #6366f1); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
.loom-select__trigger:disabled { background: var(--loom-background, #f8fafc); opacity: 0.6; cursor: not-allowed; }
.loom-select__trigger--error { border-color: var(--loom-error, #ef4444); }
.loom-select__trigger--error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }
.loom-select__trigger--open { border-color: var(--loom-primary, #6366f1); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
.loom-select__value { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.loom-select__placeholder { color: var(--loom-text-secondary, #64748b); }
.loom-select__arrow { color: var(--loom-text-secondary, #64748b); transition: transform 0.2s; flex-shrink: 0; }
.loom-select__trigger--open .loom-select__arrow { transform: rotate(180deg); }
.loom-select__dropdown {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    margin-top: 4px; padding: 4px 0; background: var(--loom-surface, #ffffff);
    border: 1px solid var(--loom-border, #e2e8f0); border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12); max-height: 240px; overflow-y: auto;
    opacity: 0; visibility: hidden; transform: translateY(-8px);
    transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
}
.loom-select__dropdown--open { opacity: 1; visibility: visible; transform: translateY(0); }
.loom-select__option {
    display: flex; align-items: center; gap: 8px; padding: 10px 12px;
    font-size: 15px; color: var(--loom-text, #1a1a1a); cursor: pointer;
    transition: background 0.15s;
}
.loom-select__option:hover { background: var(--loom-background, #f8fafc); }
.loom-select__option--selected { background: rgba(99,102,241,0.1); color: var(--loom-primary, #6366f1); font-weight: 500; }
.loom-select__option--selected:hover { background: rgba(99,102,241,0.15); }
.loom-select__option--focused { background: var(--loom-background, #f8fafc); outline: 2px solid var(--loom-primary, #6366f1); outline-offset: -2px; }
.loom-select__check { width: 16px; height: 16px; color: var(--loom-primary, #6366f1); }
.loom-select__error { font-size: 12px; color: var(--loom-error, #ef4444); margin-top: 2px; }
.loom-select__hidden { position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0; }
</style>
<script>
(function(){
    if(window.LoomSelect) return;
    window.LoomSelect = {
        init(el) {
            const trigger = el.querySelector(".loom-select__trigger");
            const dropdown = el.querySelector(".loom-select__dropdown");
            const hidden = el.querySelector(".loom-select__hidden");
            const options = el.querySelectorAll(".loom-select__option");
            const valueEl = trigger.querySelector(".loom-select__value");
            let focusIdx = -1;

            const open = () => {
                if(trigger.disabled) return;
                trigger.classList.add("loom-select__trigger--open");
                dropdown.classList.add("loom-select__dropdown--open");
                focusIdx = Array.from(options).findIndex(o => o.classList.contains("loom-select__option--selected"));
                updateFocus();
            };
            const close = () => {
                trigger.classList.remove("loom-select__trigger--open");
                dropdown.classList.remove("loom-select__dropdown--open");
                options.forEach(o => o.classList.remove("loom-select__option--focused"));
                focusIdx = -1;
            };
            const isOpen = () => dropdown.classList.contains("loom-select__dropdown--open");
            const toggle = () => isOpen() ? close() : open();

            const select = (opt) => {
                const val = opt.dataset.value;
                const label = opt.dataset.label;
                hidden.value = val;
                valueEl.textContent = label;
                valueEl.classList.remove("loom-select__placeholder");
                options.forEach(o => o.classList.remove("loom-select__option--selected"));
                opt.classList.add("loom-select__option--selected");
                close();
                hidden.dispatchEvent(new Event("change", {bubbles:true}));
            };

            const updateFocus = () => {
                options.forEach((o,i) => o.classList.toggle("loom-select__option--focused", i === focusIdx));
                if(focusIdx >= 0) options[focusIdx].scrollIntoView({block:"nearest"});
            };

            trigger.addEventListener("click", (e) => { e.stopPropagation(); toggle(); });
            trigger.addEventListener("keydown", (e) => {
                if(e.key === "Enter" || e.key === " ") { e.preventDefault(); toggle(); }
                else if(e.key === "Escape") close();
                else if(e.key === "ArrowDown") { e.preventDefault(); if(!isOpen()) open(); else { focusIdx = Math.min(focusIdx+1, options.length-1); updateFocus(); }}
                else if(e.key === "ArrowUp") { e.preventDefault(); if(isOpen()) { focusIdx = Math.max(focusIdx-1, 0); updateFocus(); }}
                else if(e.key === "Enter" && isOpen() && focusIdx >= 0) { e.preventDefault(); select(options[focusIdx]); }
            });

            options.forEach(opt => {
                opt.addEventListener("click", (e) => { e.stopPropagation(); select(opt); });
                opt.addEventListener("mouseenter", () => { focusIdx = Array.from(options).indexOf(opt); updateFocus(); });
            });

            document.addEventListener("click", (e) => { if(!el.contains(e.target)) close(); });
        }
    };
})();
</script>';
    }

    public function render(): string {
        $styles = $this->injectStyles();
        self::$instanceCount++;
        $uid = $this->id ?? 'loom-select-' . self::$instanceCount;

        $wrapperMod = ($this->modifier ?? Modifier::new())
            ->class('loom-select');

        // Find selected label
        $selectedLabel = $this->placeholder ?? 'Select...';
        $hasValue = false;
        foreach ($this->options as $key => $label) {
            $optValue = is_int($key) ? $label : $key;
            if ((string) $optValue === (string) $this->value) {
                $selectedLabel = $label;
                $hasValue = true;
                break;
            }
        }

        $content = '';

        // Label
        if ($this->label) {
            $labelContent = esc_html($this->label);
            if ($this->required) {
                $labelContent .= '<span class="loom-select__label--required"> *</span>';
            }
            $content .= '<label class="loom-select__label" for="' . esc_attr($uid) . '">' . $labelContent . '</label>';
        }

        // Hidden input for form submission
        $hiddenAttrs = ['class' => 'loom-select__hidden', 'type' => 'hidden'];
        if ($this->name) $hiddenAttrs['name'] = $this->name;
        if ($this->value !== null) $hiddenAttrs['value'] = $this->value;
        if ($this->required) $hiddenAttrs['required'] = true;
        if ($this->onChange) $hiddenAttrs['onchange'] = $this->onChange;
        $content .= $this->tag('input', '', null, $hiddenAttrs);

        // Trigger button
        $triggerClasses = 'loom-select__trigger';
        if ($this->error) $triggerClasses .= ' loom-select__trigger--error';

        $valueClasses = 'loom-select__value';
        if (!$hasValue) $valueClasses .= ' loom-select__placeholder';

        $arrow = '<svg class="loom-select__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        $triggerAttrs = ['class' => $triggerClasses, 'type' => 'button', 'id' => $uid];
        if ($this->disabled) $triggerAttrs['disabled'] = true;

        $content .= $this->tag('button',
            '<span class="' . $valueClasses . '">' . esc_html($selectedLabel) . '</span>' . $arrow,
            null, $triggerAttrs
        );

        // Dropdown panel
        $optionsHtml = '';
        $checkIcon = '<svg class="loom-select__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';

        foreach ($this->options as $key => $label) {
            $optValue = is_int($key) ? $label : $key;
            $isSelected = (string) $optValue === (string) $this->value;
            $optClasses = 'loom-select__option';
            if ($isSelected) $optClasses .= ' loom-select__option--selected';

            $optContent = '<span style="flex:1">' . esc_html($label) . '</span>';
            if ($isSelected) $optContent .= $checkIcon;

            $optionsHtml .= '<div class="' . $optClasses . '" data-value="' . esc_attr($optValue) . '" data-label="' . esc_attr($label) . '">' . $optContent . '</div>';
        }

        $content .= '<div class="loom-select__dropdown">' . $optionsHtml . '</div>';

        // Error message
        if ($this->error) {
            $content .= '<span class="loom-select__error">' . esc_html($this->error) . '</span>';
        }

        // Init script
        $content .= '<script>LoomSelect.init(document.getElementById("' . esc_attr($uid) . '").closest(".loom-select"));</script>';

        return $styles . $this->tag('div', $content, $wrapperMod);
    }
}
