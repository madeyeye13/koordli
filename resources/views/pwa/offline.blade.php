<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>You're Offline — {{ $appName }}</title>
<style>
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#FAFAF9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding:24px; }
  .card { max-width:380px; text-align:center; }
  .icon { width:56px; height:56px; margin:0 auto 20px; background:{{ $themeColor }}22; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  h1 { font-size:18px; color:#1C1917; margin:0 0 8px; }
  p { font-size:13px; color:#78716C; line-height:1.6; margin:0 0 20px; }
  button { background:{{ $themeColor }}; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-size:13px; cursor:pointer; }
</style>
</head>
<body>
  <div class="card">
    <div class="icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="{{ $themeColor }}" stroke-width="2">
        <path d="M1 1l22 22"/><path d="M16.72 11.06A10.94 10.94 0 0119 12.55"/>
        <path d="M5 12.55a10.94 10.94 0 015.17-2.39"/><path d="M10.71 5.05A16 16 0 0122.58 9"/>
        <path d="M1.42 9a15.91 15.91 0 014.7-2.88"/><path d="M8.53 16.11a6 6 0 016.95 0"/>
        <line x1="12" y1="20" x2="12.01" y2="20"/>
      </svg>
    </div>
    <h1>You're offline</h1>
    <p>{{ $appName }} needs an internet connection for this page. Check your connection and try again — your recent data is safe.</p>
    <button onclick="window.location.reload()">Try Again</button>
  </div>
</body>
</html>