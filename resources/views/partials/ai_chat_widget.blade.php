<!-- Floating AI HRIS Circular Link Button -->
<style>
  #aiChatFab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1060;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  #aiChatFab:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 14px 32px rgba(30, 58, 138, 0.55);
    color: #ffffff;
  }

  #aiChatFab:active {
    transform: scale(0.95);
  }

  .fab-pulse-ring {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid rgba(59, 130, 246, 0.5);
    animation: fabPulse 2.5s infinite ease-in-out;
    pointer-events: none;
  }

  @keyframes fabPulse {
    0%, 100% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.12); opacity: 0.8; }
  }

  .fab-badge-dot {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #22c55e;
    border: 2px solid #0f172a;
    border-radius: 50%;
  }
</style>

@if(!request()->routeIs('ai-chat.index'))
<a href="{{ route('ai-chat.index') }}" id="aiChatFab" title="Buka AI HRIS">
  <div class="fab-pulse-ring"></div>
  <i class="bi bi-chat-square-dots-fill fs-4 text-white"></i>
  <span class="fab-badge-dot"></span>
</a>
@endif
