<?php

namespace App\Livewire\Shared;

use Livewire\Component;

class Dropdown extends Component
{
    public string  $name          = '';
    public mixed   $value         = null;
    public array   $options       = [];
    public string  $placeholder   = 'Select an option';
    public bool    $searchable    = false;
    public bool    $multiple      = false;
    public bool    $open          = false;
    public string  $search        = '';
    public string  $optionLabel   = 'label';
    public string  $optionValue   = 'value';

    public function mount(
        string $name,
        mixed $value = null,
        array $options = [],
        string $placeholder = 'Select an option',
        bool $searchable = false,
        bool $multiple = false,
        string $optionLabel = 'label',
        string $optionValue = 'value',
    ): void {
        $this->name        = $name;
        $this->value       = $value ?? ($multiple ? [] : null);
        $this->options     = $options;
        $this->placeholder = $placeholder;
        $this->searchable  = $searchable;
        $this->multiple    = $multiple;
        $this->optionLabel = $optionLabel;
        $this->optionValue = $optionValue;
    }

    public function toggle(): void
    {
        $this->open  = !$this->open;
        $this->search = '';
    }

    public function select(mixed $optionValue): void
    {
        if ($this->multiple) {
            if (in_array($optionValue, (array) $this->value)) {
                $this->value = array_values(
                    array_filter((array) $this->value, fn($v) => $v !== $optionValue)
                );
            } else {
                $this->value = [...(array) $this->value, $optionValue];
            }
        } else {
            $this->value = $optionValue;
            $this->open  = false;
        }

        $this->dispatch('dropdown-changed', [
            'name'  => $this->name,
            'value' => $this->value,
        ]);
    }

    public function clear(): void
    {
        $this->value = $this->multiple ? [] : null;
        $this->dispatch('dropdown-changed', [
            'name'  => $this->name,
            'value' => $this->value,
        ]);
    }

    public function getFilteredOptionsProperty(): array
    {
        if (!$this->searchable || empty($this->search)) {
            return $this->options;
        }

        return array_values(array_filter(
            $this->options,
            fn($option) => str_contains(
                strtolower($option[$this->optionLabel] ?? ''),
                strtolower($this->search)
            )
        ));
    }

    public function getSelectedLabelProperty(): string
    {
        if ($this->multiple) {
            $selected = array_filter(
                $this->options,
                fn($o) => in_array($o[$this->optionValue], (array) $this->value)
            );
            if (empty($selected)) return '';
            return implode(', ', array_column($selected, $this->optionLabel));
        }

        $found = array_filter(
            $this->options,
            fn($o) => $o[$this->optionValue] == $this->value
        );

        return array_values($found)[0][$this->optionLabel] ?? '';
    }

    public function render()
    {
        return view('livewire.shared.dropdown');
    }
}