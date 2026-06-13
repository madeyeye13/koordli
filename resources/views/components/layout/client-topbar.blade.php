<div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">

    <div style="display: flex; align-items: center; gap: 12px;">
        <button
            class="krd-hamburger"
            x-on:click="sidebarOpen = !sidebarOpen"
            style="background:none;border:none;cursor:pointer;padding:4px;display:flex;align-items:center;color:#57534E;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div style="font-size: 13px; font-weight: 500; color: #1C1917;">
            Client Portal
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 12px;">
        <button
            x-on:click="$store.theme.toggle()"
            style="background:none;border:none;cursor:pointer;color:#78716C;display:flex;align-items:center;padding:6px;">
            <svg x-show="!$store.theme.dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
            <svg x-show="$store.theme.dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </button>
    </div>

</div>