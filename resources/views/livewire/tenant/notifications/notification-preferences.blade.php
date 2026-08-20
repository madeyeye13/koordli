<div>
    <div style="margin-bottom:24px;">
        <div class="krd-label" style="margin-bottom:4px;">Settings</div>
        <h2 class="krd-heading-3" style="color:#1C1917;">Notification Preferences</h2>
        <p style="font-size:13px;color:#78716C;margin-top:4px;">Choose how you want to be notified for each type of activity.</p>
    </div>

    <div class="krd-card" style="padding:0;overflow:hidden;max-width:640px;">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th style="text-align:center;">In-App</th>
                        <th style="text-align:center;">Email</th>
                        <th style="text-align:center;">Real-time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $key => $label)
                    <tr>
                        <td style="font-size:13px;font-weight:500;color:#1C1917;">{{ $label }}</td>
                        @foreach(['database' => 'In-App', 'mail' => 'Email', 'broadcast' => 'Real-time'] as $channel => $chLabel)
                        <td style="text-align:center;">
                            <input type="checkbox"
                                wire:click="toggleChannel('{{ $key }}', '{{ $channel }}')"
                                {{ in_array($channel, $preferences[$key] ?? []) ? 'checked' : '' }}
                                style="width:16px;height:16px;cursor:pointer;accent-color:#7C3AED;" />
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>