export function DormantTrace({ className }: { className?: string }) {
    return (
        <svg viewBox="0 0 240 40" className={className} aria-hidden="true">
            <line
                x1="0"
                y1="20"
                x2="240"
                y2="20"
                className="stroke-border"
                strokeWidth={1.5}
                strokeDasharray="1 7"
                strokeLinecap="round"
            />
            <circle
                cx="120"
                cy="20"
                r="4"
                className="animate-pulse-dot fill-signal"
                style={{
                    filter: 'drop-shadow(0 0 6px rgba(255, 122, 51, 0.55))',
                }}
            />
        </svg>
    );
}
