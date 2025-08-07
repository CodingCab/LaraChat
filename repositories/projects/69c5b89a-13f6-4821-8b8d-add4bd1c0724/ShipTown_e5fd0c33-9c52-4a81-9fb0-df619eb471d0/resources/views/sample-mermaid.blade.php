@extends('layouts.auth')

@section('title', t('Sample Order Flowchart'))

@section('content')
<div class="container py-5">
    <h1 class="mb-4">{{ t('Order Flowchart') }}</h1>
    <div class="mermaid">
        {!! $chart !!}
    </div>
</div>

<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
mermaid.initialize({ startOnLoad: true });

// Zoom support
function addMermaidZoom() {
    const mermaidDiv = document.querySelector('.mermaid');
    if (!mermaidDiv) return;
    let scale = 1;
    let originX = 0;
    let originY = 0;
    let isPanning = false;
    let startX, startY;
    let lastOriginX = 0;
    let lastOriginY = 0;

    mermaidDiv.style.transformOrigin = '0 0';
    mermaidDiv.style.transition = 'transform 0.1s';

    mermaidDiv.addEventListener('wheel', function(e) {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const mouseX = e.clientX;
            const mouseY = e.clientY;
            const prevScale = scale;
            // Make zoom speed depend on current scale: slower at low zoom, faster at high zoom
            let baseDelta = e.deltaY > 0 ? -0.2 : 0.12;
            // Adjust speed: multiply by (0.5 + scale) so at scale=1 it's normal, at scale=0.2 it's slow, at scale=5 it's fast
            let delta = baseDelta * (0.5 + scale);
            scale = Math.max(0.2, Math.min(5, scale + delta));
            originX = originX - (mouseX / prevScale - mouseX / scale);
            originY = originY - (mouseY / prevScale - mouseY / scale);
            mermaidDiv.style.transform = `scale(${scale}) translate(${originX}px, ${originY}px)`;
        }
    }, { passive: false });

    mermaidDiv.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return; // Only left mouse button
        isPanning = true;
        startX = e.clientX;
        startY = e.clientY;
        lastOriginX = originX;
        lastOriginY = originY;
        document.body.style.cursor = 'grab';
        e.preventDefault();
    });
    window.addEventListener('mousemove', function(e) {
        if (!isPanning) return;
        originX = lastOriginX + (e.clientX - startX) / scale;
        originY = lastOriginY + (e.clientY - startY) / scale;
        mermaidDiv.style.transform = `scale(${scale}) translate(${originX}px, ${originY}px)`;
    });
    window.addEventListener('mouseup', function() {
        isPanning = false;
        document.body.style.cursor = '';
    });
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === '0') {
            scale = 1;
            originX = 0;
            originY = 0;
            mermaidDiv.style.transform = `scale(${scale}) translate(${originX}px, ${originY}px)`;
            e.preventDefault();
        }
    });
}

document.addEventListener('DOMContentLoaded', addMermaidZoom);
</script>
@endsection
