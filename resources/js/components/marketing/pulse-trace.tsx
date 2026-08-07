function buildTracePath(units: number, unitWidth: number, baseline: number) {
    let d = `M0,${baseline}`;

    for (let i = 0; i < units; i++) {
        const x0 = i * unitWidth;

        d += ` L${x0 + 140},${baseline}`;
        d += ` L${x0 + 150},${baseline - 10}`;
        d += ` L${x0 + 158},${baseline + 30}`;
        d += ` L${x0 + 166},${baseline - 50}`;
        d += ` L${x0 + 174},${baseline + 6}`;
        d += ` L${x0 + 182},${baseline}`;
    }

    d += ` L${units * unitWidth},${baseline}`;

    return d;
}

const TRACE_PATH = buildTracePath(6, 200, 60);

export function PulseTrace({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 1200 120"
            preserveAspectRatio="none"
            className={className}
            aria-hidden="true"
        >
            <path
                d={TRACE_PATH}
                fill="none"
                className="stroke-[#242A33]"
                strokeWidth={1.5}
                vectorEffect="non-scaling-stroke"
            />
            <path
                d={TRACE_PATH}
                fill="none"
                className="animate-trace-sweep stroke-[#FF7A33]"
                strokeWidth={1.5}
                strokeLinecap="round"
                strokeDasharray="90 1110"
                vectorEffect="non-scaling-stroke"
                style={{
                    filter: 'drop-shadow(0 0 6px rgba(255, 122, 51, 0.65))',
                }}
            />
        </svg>
    );
}
