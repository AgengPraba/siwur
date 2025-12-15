<?php
$string = "
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;

new #[Layout('components.layouts.app')] #[Title('Home')] class extends Component {
    //
}; ?>

<div>
    <x-header title=\"Home\" separator progress-indicator>

    </x-header>
    <div class=\"grid grid-cols-1 md:grid-cols-12 gap-4 pb-4\">
        <div class=\"md:col-span-4\">
            <x-card title=\"Your stats\" subtitle=\"Our findings about you\" shadow separator>
                I have title, subtitle and separator.
            </x-card>
        </div>

        <div class=\"md:col-span-8\">
            <x-card title=\"Nice things\">
                Lorem Ipsum Dolor
                <x-slot:menu>
                    <x-button icon=\"o-share\" class=\"btn-circle btn-sm\" />
                    <x-icon name=\"o-heart\" class=\"cursor-pointer\" />
                </x-slot:menu>
                <x-slot:actions separator>
                    <x-button label=\"Ok\" class=\"btn-primary\" />
                </x-slot:actions>
            </x-card>
        </div>
    </div>

    <div class=\"grid grid-cols-1 md:grid-cols-12 gap-4 pb-4\">
        <div class=\"md:col-span-3\">
            <x-stat title=\"Messages\" description=\"This month\" value=\"44\" icon=\"o-envelope\" tooltip=\"Hello\"
                color=\"text-primary\" />
        </div>
        <div class=\"md:col-span-3\">
            <x-stat title=\"Sales\" description=\"This month\" value=\"22.124\" icon=\"o-arrow-trending-up\"
                tooltip-bottom=\"There\" />
        </div>
        <div class=\"md:col-span-3\">
            <x-stat title=\"Lost\" description=\"This month\" value=\"34\" icon=\"o-arrow-trending-down\"
                tooltip-left=\"Ops\" />
        </div>
        <div class=\"md:col-span-3\">
            <x-stat title=\"Sales\" description=\"This month\" value=\"22.124\" icon=\"o-arrow-trending-down\"
                class=\"text-orange-500\" color=\"text-pink-500\" tooltip-right=\"woke\" />
        </div>
    </div>
</div>
";

$hasil_view_form = createFile($string, "../resources/views/livewire/home.blade.php");
