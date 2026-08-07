export function PulseUnderline({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 160 16"
            preserveAspectRatio="none"
            className={className}
            aria-hidden="true"
        >
            <path
                d="M0,8 L60,8 L66,3 L72,13 L78,1 L84,10 L90,8 L160,8"
                fill="none"
                className="stroke-[#FF7A33]"
                strokeWidth={2.5}
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}
