<script>
    (() => {
        const W = 302;
        const H = 78;

        const debounce = (fn, wait = 120) => {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), wait);
            };
        };

        const resize = () => {
            document.querySelectorAll('.recaptcha-wrap').forEach(wrap => {
                const widget = wrap.querySelector('.g-recaptcha');
                if (!widget) return;

                const containerWidth = wrap.clientWidth || W;

                const scale = containerWidth / W;

                widget.style.transform = `scale(${scale})`;
                wrap.style.height = `${H * scale}px`;
            });
        };

        window.addEventListener('load', resize);
        window.addEventListener('resize', debounce(resize));
    })();
</script>
