{{-- MODAL DE PAGAMENTO --}}
<div class="ol-modal" x-show="pay.show" x-transition.opacity
     @keydown.escape.window="pay.show=false" @click.self="pay.show=false">
  <div class="ol-modal-box">
    <div class="ol-modal-head">
      <h3 x-text="pay.title"></h3>
      <button class="btn btn-soft" @click="pay.show=false">✕</button>
    </div>

    {{-- PIX --}}
    <template x-if="pay.mode==='pix'">
      <div class="pix-box" id="printable-pay">
        <img class="pix-qr" :src="pay.qr_base64" alt="QR Pix">
        <div class="pair"><span>Total</span><strong x-text="money(pay.total)"></strong></div>
        <div class="pair" x-show="pay.expires_at"><span>Válido até</span><span x-text="pay.expires_at"></span></div>

        <label class="label">Código Pix (copia e cola)</label>
        <textarea class="input pix-code" rows="3" x-model="pay.copia_cola" readonly></textarea>

        <div class="row">
          <button class="btn btn-primary" @click="copyPix()">Copiar código Pix</button>
          <button class="btn btn-soft"    @click="printPay()">Imprimir</button>
          <button class="btn btn-soft"    @click="sendWhatsApp()">Enviar no WhatsApp</button>
          <small class="muted" x-show="pay.copied">copiado ✔</small>
        </div>
      </div>
    </template>

    {{-- LINK MP --}}
    <template x-if="pay.mode==='link'">
      <div class="link-box" id="printable-pay">
        <div class="pair"><span>Total</span><strong x-text="money(pay.total)"></strong></div>
        <a class="btn btn-primary" :href="pay.link" target="_blank" rel="noopener">Abrir link de pagamento</a>
        <div class="row">
          <button class="btn btn-soft" @click="copyLink()">Copiar link</button>
          <button class="btn btn-soft" @click="printPay()">Imprimir</button>
          <button class="btn btn-soft" @click="sendWhatsApp()">Enviar no WhatsApp</button>
        </div>
        <small class="muted">Checkout Mercado Pago com cartão (crédito/débito) e Pix; boleto desativado.</small>
      </div>
    </template>
  </div>
</div>
