@props(['wireModel', 'label' => 'Your Signature'])

<div x-data="{
    mode: 'draw',
    canvas: null,
    ctx: null,
    drawing: false,
    hasDrawn: false,
    typedName: '',

    initCanvas() {
        this.canvas = this.$refs.sigCanvas;
        this.ctx = this.canvas.getContext('2d');
        this.ctx.strokeStyle = '#1C1917';
        this.ctx.lineWidth = 2.5;
        this.ctx.lineCap = 'round';
    },
    getPos(e) {
        const rect = this.canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: clientX - rect.left, y: clientY - rect.top };
    },
    startDraw(e) {
        this.drawing = true;
        const pos = this.getPos(e);
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);
    },
    draw(e) {
        if (!this.drawing) return;
        e.preventDefault();
        const pos = this.getPos(e);
        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();
        this.hasDrawn = true;
    },
    stopDraw() {
        this.drawing = false;
        if (this.hasDrawn) {
            $wire.set('{{ $wireModel }}', this.canvas.toDataURL('image/png'));
            $wire.set('{{ $wireModel }}_type', 'draw');
        }
    },
    clearCanvas() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.hasDrawn = false;
        $wire.set('{{ $wireModel }}', '');
    },
    setTyped() {
        $wire.set('{{ $wireModel }}', this.typedName);
        $wire.set('{{ $wireModel }}_type', 'type');
    }
}" x-init="$nextTick(() => initCanvas())">

    <label class="krd-label-text">{{ $label }}</label>

    <div style="display:flex;gap:4px;background:#F5F5F4;border-radius:6px;padding:3px;margin-bottom:10px;width:fit-content;">
        <button type="button" x-on:click="mode = 'draw'"
            :style="mode === 'draw' ? 'padding:5px 14px;border-radius:4px;border:none;background:#fff;color:#1C1917;font-size:12px;font-weight:600;cursor:pointer;' : 'padding:5px 14px;border-radius:4px;border:none;background:transparent;color:#78716C;font-size:12px;cursor:pointer;'">
            ✍️ Draw
        </button>
        <button type="button" x-on:click="mode = 'type'"
            :style="mode === 'type' ? 'padding:5px 14px;border-radius:4px;border:none;background:#fff;color:#1C1917;font-size:12px;font-weight:600;cursor:pointer;' : 'padding:5px 14px;border-radius:4px;border:none;background:transparent;color:#78716C;font-size:12px;cursor:pointer;'">
            ⌨️ Type
        </button>
    </div>

    <div x-show="mode === 'draw'" x-cloak>
        <canvas x-ref="sigCanvas" width="400" height="140"
            style="border:1.5px dashed #D6D3D1;border-radius:6px;width:100%;max-width:400px;height:140px;background:#fff;touch-action:none;cursor:crosshair;"
            x-on:mousedown="startDraw($event)"
            x-on:mousemove="draw($event)"
            x-on:mouseup="stopDraw()"
            x-on:mouseleave="stopDraw()"
            x-on:touchstart="startDraw($event)"
            x-on:touchmove="draw($event)"
            x-on:touchend="stopDraw()">
        </canvas>
        <button type="button" x-on:click="clearCanvas()"
            style="margin-top:6px;font-size:11px;color:#78716C;background:none;border:none;cursor:pointer;text-decoration:underline;">
            Clear
        </button>
    </div>

    <div x-show="mode === 'type'" x-cloak style="max-width:400px;">
        <input type="text" x-model="typedName" x-on:input="setTyped()"
            placeholder="Type your full name"
            style="width:100%;padding:16px 14px;border:1.5px dashed #D6D3D1;border-radius:6px;font-family:'Brush Script MT', cursive;font-size:26px;color:#1C1917;background:#fff;" />
    </div>
</div>