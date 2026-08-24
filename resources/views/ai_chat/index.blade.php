@extends('layouts.app')

@section('title', 'AI HRIS - Asisten Cerdas HRIS')

@section('content')
<style>
  .ai-chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 165px);
    min-height: 550px;
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    overflow: hidden;
  }

  .ai-chat-header-bar {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
    color: #ffffff;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .ai-avatar-box {
    width: 48px;
    height: 48px;
    min-width: 48px;
    min-height: 48px;
    aspect-ratio: 1 / 1;
    flex-shrink: 0;
    background: #ffffff;
    border-radius: 16px;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    position: relative;
  }

  .ai-avatar-box img {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    flex-shrink: 0;
    display: block;
  }

  .status-online-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 13px;
    height: 13px;
    background: #22c55e;
    border: 2.5px solid #0f172a;
    border-radius: 50%;
  }

  .ai-messages-area {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .chat-bubble {
    max-width: 80%;
    padding: 14px 20px;
    border-radius: 20px;
    font-size: 14px;
    line-height: 1.6;
    word-break: break-word;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
  }

  .chat-bubble-bot {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 6px;
    align-self: flex-start;
  }

  .chat-bubble-user {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: #ffffff;
    border-bottom-right-radius: 6px;
    align-self: flex-end;
  }

  .chat-time {
    font-size: 10.5px;
    opacity: 0.7;
    margin-top: 6px;
    text-align: right;
  }

  .quick-prompts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    margin-top: 12px;
  }

  .prompt-card-btn {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e3a8a;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px 14px;
    border-radius: 14px;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .prompt-card-btn:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
  }

  .typing-indicator-box {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 12px 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    border-bottom-left-radius: 6px;
    align-self: flex-start;
  }

  .typing-dot-item {
    width: 7px;
    height: 7px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out both;
  }

  .typing-dot-item:nth-child(1) { animation-delay: -0.32s; }
  .typing-dot-item:nth-child(2) { animation-delay: -0.16s; }

  @keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
  }

  .ai-footer-input-bar {
    padding: 16px 24px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
  }

  .ai-form-input-group {
    display: flex;
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 18px;
    padding: 6px 8px 6px 18px;
    transition: all 0.2s ease;
  }

  .ai-form-input-group:focus-within {
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
  }

  .ai-form-input-group input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1;
    font-size: 15px;
    color: #0f172a;
  }

  .btn-submit-chat {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  }

  .btn-submit-chat:hover {
    transform: scale(1.04);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
  }
</style>

<div class="container-fluid px-0">
  <div class="ai-chat-container">
    <!-- Header Bar -->
    <div class="ai-chat-header-bar">
      <div class="d-flex align-items-center gap-2 gap-sm-3 me-2" style="min-width: 0;">
        <div class="ai-avatar-box">
          <img src="{{ asset('assets/logo.png') }}" alt="Logo IBP">
          <div class="status-online-badge"></div>
        </div>
        <div style="min-width: 0;">
          <h5 class="fw-bold mb-0 text-white text-truncate" style="font-size: 16px; letter-spacing: -0.3px;">AI HRIS</h5>
          <span class="text-white-50 text-truncate d-block" style="font-size: 11px;"><i class="bi bi-circle-fill text-success me-1" style="font-size: 7px;"></i> Asisten Cerdas HRIS • Online 24/7</span>
        </div>
      </div>
      <div class="d-none d-sm-block flex-shrink-0">
        <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-semibold shadow-sm">
          <i class="bi bi-chat-dots me-1"></i> Asisten Otomatis
        </span>
      </div>
    </div>

    <!-- Messages Area -->
    <div class="ai-messages-area" id="aiFullChatBody">
      <!-- Default Bot Welcome Bubble -->
      <div class="chat-bubble chat-bubble-bot">
        Halo <b>{{ auth()->user()->name }}</b> 👋! Saya <b>AI HRIS</b> PT Inti Bumi Perkasa. Silakan ketikkan pertanyaan atau masalah yang ingin Anda tanyakan di bawah ini.
        <div class="chat-time">{{ now()->format('H:i') }}</div>
      </div>
    </div>

    <!-- Footer Input Bar -->
    <div class="ai-footer-input-bar">
      <form id="aiFullChatForm" onsubmit="handleFullAiChatSubmit(event)">
        <div class="ai-form-input-group">
          <input type="text" id="aiFullChatInput" placeholder="Tanyakan sesuatu pada AI HRIS..." autocomplete="off" required autofocus>
          <button type="submit" class="btn-submit-chat" id="btnSubmitFullChat">
            <i class="bi bi-send-fill"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@section('scripts')
<script>
  function parseMarkdown(text) {
    return text
      .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
      .replace(/\*(.*?)\*/g, '<i>$1</i>')
      .replace(/\n/g, '<br>');
  }

  function appendFullUserMessage(text) {
    const body = document.getElementById('aiFullChatBody');
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const div = document.createElement('div');
    div.className = 'chat-bubble chat-bubble-user';
    div.innerHTML = `${parseMarkdown(text)}<div class="chat-time">${time}</div>`;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
  }

  function appendFullBotMessage(text) {
    const body = document.getElementById('aiFullChatBody');
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const div = document.createElement('div');
    div.className = 'chat-bubble chat-bubble-bot';
    div.innerHTML = `${parseMarkdown(text)}<div class="chat-time">${time}</div>`;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
  }

  function showFullTypingIndicator() {
    const body = document.getElementById('aiFullChatBody');
    const div = document.createElement('div');
    div.id = 'fullTypingIndicator';
    div.className = 'typing-indicator-box';
    div.innerHTML = `
      <div class="typing-dot-item"></div>
      <div class="typing-dot-item"></div>
      <div class="typing-dot-item"></div>
    `;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
  }

  function hideFullTypingIndicator() {
    const el = document.getElementById('fullTypingIndicator');
    if (el) el.remove();
  }

  function sendFullQuickPrompt(promptText) {
    document.getElementById('aiFullChatInput').value = promptText;
    handleFullAiChatSubmit(new Event('submit'));
  }

  async function handleFullAiChatSubmit(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('aiFullChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    appendFullUserMessage(msg);
    input.value = '';
    showFullTypingIndicator();

    try {
      const response = await fetch("{{ route('ai-chat.send') }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ message: msg })
      });

      const data = await response.json();
      hideFullTypingIndicator();

      if (data.success && data.reply) {
        appendFullBotMessage(data.reply);
      } else {
        appendFullBotMessage(data.reply || "Maaf, terjadi kendala saat memproses jawaban.");
      }
    } catch (err) {
      hideFullTypingIndicator();
      appendFullBotMessage("Maaf, terjadi kesalahan koneksi. Silakan coba beberapa saat lagi.");
    }
  }
</script>
@endsection
@endsection
