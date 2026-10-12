<?php
/**
 * Online çözümler bölümü için temsili ekran çizimleri.
 * Panelden gerçek ekran görüntüsü yüklenirse bunların yerine o kullanılır.
 */

function mockup_dashboard(): string
{
    return <<<'SVG'
<svg class="mock mock--desk" viewBox="0 0 640 400" role="img" aria-label="Firma ekranı: servislerin harita ve liste görünümü">
  <defs>
    <clipPath id="mdClip"><rect width="640" height="400" rx="14"/></clipPath>
  </defs>
  <g clip-path="url(#mdClip)">
    <rect width="640" height="400" fill="#ffffff"/>
    <rect width="640" height="34" fill="#eef1ef"/>
    <circle cx="20" cy="17" r="5" fill="#e5675b"/><circle cx="37" cy="17" r="5" fill="#e9b949"/><circle cx="54" cy="17" r="5" fill="#59b36a"/>
    <rect x="200" y="10" width="240" height="14" rx="7" fill="#ffffff"/>
    <!-- kenar menü -->
    <rect x="0" y="34" width="92" height="366" fill="#23272a"/>
    <rect x="18" y="52" width="26" height="26" rx="6" fill="#018231"/>
    <path transform="translate(18 52) scale(.406)" d="M16 14h9v16c0 3 2 5 5 5h4c3 0 5-2 5-5V14h9v24c0 7-5 12-12 12H20v-7h19c1 0 2-1 2-2v-1c-1 1-3 1-5 1h-8c-7 0-12-5-12-12z" fill="#fff"/>
    <g fill="#ffffff" opacity=".22">
      <rect x="18" y="98" width="56" height="7" rx="3.5"/><rect x="18" y="120" width="44" height="7" rx="3.5"/>
      <rect x="18" y="142" width="52" height="7" rx="3.5"/><rect x="18" y="164" width="38" height="7" rx="3.5"/>
      <rect x="18" y="186" width="48" height="7" rx="3.5"/>
    </g>
    <rect x="12" y="92" width="68" height="19" rx="5" fill="#018231" opacity=".55"/>
    <!-- özet kartları -->
    <g>
      <rect x="108" y="50" width="160" height="62" rx="8" fill="#018231"/>
      <rect x="122" y="64" width="64" height="7" rx="3.5" fill="#fff" opacity=".6"/>
      <rect x="122" y="80" width="40" height="18" rx="4" fill="#fff"/>
      <rect x="280" y="50" width="160" height="62" rx="8" fill="#f1f3f1"/>
      <rect x="294" y="64" width="70" height="7" rx="3.5" fill="#9aa0a3"/>
      <rect x="294" y="80" width="52" height="18" rx="4" fill="#23272a"/>
      <rect x="452" y="50" width="172" height="62" rx="8" fill="#f1f3f1"/>
      <rect x="466" y="64" width="58" height="7" rx="3.5" fill="#9aa0a3"/>
      <g fill="#018231"><rect x="466" y="92" width="10" height="8" rx="2"/><rect x="480" y="86" width="10" height="14" rx="2"/><rect x="494" y="81" width="10" height="19" rx="2"/><rect x="508" y="88" width="10" height="12" rx="2"/><rect x="522" y="78" width="10" height="22" rx="2"/></g>
    </g>
    <!-- harita -->
    <rect x="108" y="124" width="332" height="262" rx="8" fill="#e6ece6"/>
    <g stroke="#ffffff" stroke-width="9" fill="none" stroke-linecap="round">
      <path d="M108 190 L440 160"/><path d="M108 300 L440 330"/><path d="M180 124 L210 386"/><path d="M330 124 L300 386"/><path d="M400 124 L420 386"/>
    </g>
    <path d="M150 124 Q160 230 108 260" stroke="#cfe0f0" stroke-width="16" fill="none"/>
    <rect x="232" y="206" width="52" height="38" rx="4" fill="#d3e6cf"/><rect x="340" y="250" width="40" height="30" rx="4" fill="#d3e6cf"/>
    <path d="M150 340 C 200 300, 205 230, 255 200 S 350 175, 395 150" stroke="#018231" stroke-width="5" fill="none" stroke-dasharray="1 9" stroke-linecap="round"/>
    <circle cx="150" cy="340" r="7" fill="#fff" stroke="#23272a" stroke-width="3"/>
    <circle cx="395" cy="150" r="8" fill="#018231" stroke="#fff" stroke-width="3"/>
    <circle cx="255" cy="200" r="11" fill="#f2b705" stroke="#23272a" stroke-width="3.5"/>
    <circle cx="350" cy="300" r="9" fill="#f2b705" stroke="#23272a" stroke-width="3"/>
    <circle cx="190" cy="160" r="9" fill="#f2b705" stroke="#23272a" stroke-width="3"/>
    <!-- servis listesi -->
    <rect x="452" y="124" width="172" height="262" rx="8" fill="#f8f9f8"/>
    <rect x="466" y="138" width="70" height="8" rx="4" fill="#23272a"/>
    <g>
      <rect x="462" y="158" width="152" height="40" rx="6" fill="#fff"/><circle cx="478" cy="178" r="5" fill="#3ddc84"/><rect x="490" y="170" width="78" height="7" rx="3.5" fill="#23272a"/><rect x="490" y="182" width="52" height="6" rx="3" fill="#b8bec1"/>
      <rect x="462" y="206" width="152" height="40" rx="6" fill="#fff"/><circle cx="478" cy="226" r="5" fill="#3ddc84"/><rect x="490" y="218" width="66" height="7" rx="3.5" fill="#23272a"/><rect x="490" y="230" width="60" height="6" rx="3" fill="#b8bec1"/>
      <rect x="462" y="254" width="152" height="40" rx="6" fill="#fff"/><circle cx="478" cy="274" r="5" fill="#f2b705"/><rect x="490" y="266" width="84" height="7" rx="3.5" fill="#23272a"/><rect x="490" y="278" width="44" height="6" rx="3" fill="#b8bec1"/>
      <rect x="462" y="302" width="152" height="40" rx="6" fill="#fff"/><circle cx="478" cy="322" r="5" fill="#3ddc84"/><rect x="490" y="314" width="70" height="7" rx="3.5" fill="#23272a"/><rect x="490" y="326" width="56" height="6" rx="3" fill="#b8bec1"/>
    </g>
  </g>
  <rect x=".5" y=".5" width="639" height="399" rx="14" fill="none" stroke="#d8ddd9"/>
</svg>
SVG;
}

function mockup_phone(): string
{
    return <<<'SVG'
<svg class="mock mock--phone" viewBox="0 0 220 440" role="img" aria-label="Yolcu ekranı: servisin yaklaşma durumu">
  <rect width="220" height="440" rx="34" fill="#23272a"/>
  <defs><clipPath id="mpClip"><rect x="9" y="9" width="202" height="422" rx="26"/></clipPath></defs>
  <g clip-path="url(#mpClip)">
    <rect x="9" y="9" width="202" height="422" fill="#e6ece6"/>
    <g stroke="#fff" stroke-width="8" fill="none" stroke-linecap="round">
      <path d="M9 120 L211 96"/><path d="M9 220 L211 240"/><path d="M70 9 L92 300"/><path d="M160 9 L150 300"/>
    </g>
    <rect x="100" y="140" width="38" height="44" rx="4" fill="#d3e6cf"/>
    <path d="M40 270 C 70 230, 80 170, 120 140 S 170 100, 180 70" stroke="#018231" stroke-width="5" fill="none" stroke-dasharray="1 9" stroke-linecap="round"/>
    <circle cx="180" cy="70" r="8" fill="#018231" stroke="#fff" stroke-width="3"/>
    <circle cx="96" cy="182" r="22" fill="#f2b705" opacity=".3"/>
    <circle cx="96" cy="182" r="12" fill="#f2b705" stroke="#23272a" stroke-width="3.5"/>
    <!-- üst çubuk -->
    <rect x="9" y="9" width="202" height="44" fill="#ffffff" opacity=".9"/>
    <rect x="80" y="16" width="60" height="16" rx="8" fill="#23272a"/>
    <!-- alt kart -->
    <rect x="9" y="262" width="202" height="169" fill="#ffffff"/>
    <rect x="95" y="272" width="30" height="4" rx="2" fill="#d8ddd9"/>
    <text x="24" y="304" font-family="Source Sans 3, Arial, sans-serif" font-size="13" fill="#6b6f72">Servisiniz yaklaşıyor</text>
    <text x="24" y="336" font-family="Saira, Arial, sans-serif" font-weight="800" font-size="30" fill="#23272a">4 dk</text>
    <rect x="120" y="314" width="76" height="24" rx="3" fill="#fff" stroke="#23272a" stroke-width="1.5"/>
    <rect x="120" y="314" width="9" height="24" fill="#1d4f91"/>
    <text x="134" y="331" font-family="Saira, Arial, sans-serif" font-weight="700" font-size="11" fill="#23272a">34 ABC 123</text>
    <rect x="24" y="352" width="172" height="6" rx="3" fill="#eef1ef"/>
    <rect x="24" y="352" width="128" height="6" rx="3" fill="#018231"/>
    <rect x="24" y="374" width="172" height="38" rx="10" fill="#018231"/>
    <text x="110" y="398" text-anchor="middle" font-family="Source Sans 3, Arial, sans-serif" font-weight="700" font-size="13" fill="#fff">Servis sorumlusunu ara</text>
  </g>
</svg>
SVG;
}
