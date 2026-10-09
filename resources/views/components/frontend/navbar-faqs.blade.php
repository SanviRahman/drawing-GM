{{--
    Usage on each real public page, after its main content:
      <x-frontend.navbar-faqs />
    For CMS Pages that have their own FAQ mapping, explicitly pass the page ID:
      <x-frontend.navbar-faqs :page-id="$page->id" />
    With alias/customized routing, explicitly pass the selected primary menu-item ID:
      <x-frontend.navbar-faqs :menu-item-id="$primaryMenuItem->id" />
    No public page is generated here: the existing ZIP currently serves welcome.blade.php at /.
--}}
@props(['menuItemId' => null, 'pageId' => null, 'heading' => 'Frequently Asked Questions'])
@php
    $resolvedNavFaqs = app(\App\Services\Frontend\NavbarFaqs::class)
        ->forRequest(request(), $menuItemId === null ? null : (int) $menuItemId, $pageId === null ? null : (int) $pageId);
@endphp
@if($resolvedNavFaqs)
    <section class="nav-faqs" aria-labelledby="nav-faqs-title-{{ ($resolvedNavFaqs['menuItem']->id ?? 'page-'.$resolvedNavFaqs['page']->id) }}">
        <div class="nav-faqs__inner">
            <h2 class="nav-faqs__heading" id="nav-faqs-title-{{ ($resolvedNavFaqs['menuItem']->id ?? 'page-'.$resolvedNavFaqs['page']->id) }}">{{ $heading }}</h2>
            <div class="nav-faqs__items">
                @foreach($resolvedNavFaqs['faqs'] as $faq)
                    <details class="nav-faqs__item">
                        <summary class="nav-faqs__question">
                            {{ trim(html_entity_decode(strip_tags((string) $faq->question), ENT_QUOTES | ENT_HTML5, 'UTF-8')) }}
                        </summary>
                        <div class="nav-faqs__answer">
                            @php
                                // Never output untrusted HTML from previously saved FAQ records into a public page.
                                $answerWithBreaks = preg_replace('/<\/(p|div|li|h[1-6])\s*>/iu', "\n", (string) $faq->answer);
                                $plainAnswer = trim(html_entity_decode(strip_tags((string) $answerWithBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                            @endphp
                            {{ $plainAnswer }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
    @once
        <style>
            .nav-faqs { padding: clamp(3rem, 6vw, 5rem) 1rem; background: var(--faq-section-background, #fbf8f3); color: var(--faq-section-ink, #203a46); }
            .nav-faqs__inner { max-width: 1000px; margin-inline: auto; }
            .nav-faqs__heading { font-size: clamp(1.7rem, 3vw, 2.4rem); font-weight: 700; line-height: 1.25; margin: 0 0 1.5rem; }
            .nav-faqs__items { display: grid; gap: .85rem; }
            .nav-faqs__item { background: #fff; border: 1px solid #dce4e0; border-radius: 12px; overflow: hidden; }
            .nav-faqs__question { cursor: pointer; padding: 1.1rem 1.3rem; font-size: 1.05rem; font-weight: 650; line-height: 1.5; }
            .nav-faqs__question:focus-visible { outline: 3px solid #216d61; outline-offset: -3px; }
            .nav-faqs__answer { padding: 0 1.3rem 1.25rem; line-height: 1.75; color: #21343c; white-space: pre-line; overflow-wrap: anywhere; }
            @media (max-width: 640px) { .nav-faqs__question { padding: 1rem; } .nav-faqs__answer { padding: 0 1rem 1rem; } }
        </style>
    @endonce
@endif
