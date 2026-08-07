export function SectionHeading({
    eyebrow,
    title,
    description,
    id,
}: {
    eyebrow: string;
    title: string;
    description?: string;
    id?: string;
}) {
    return (
        <div id={id} className="max-w-2xl scroll-mt-24">
            <p className="font-mono text-[11px] tracking-[0.18em] text-[#FF7A33] uppercase">
                {eyebrow}
            </p>
            <h2 className="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
                {title}
            </h2>
            {description && (
                <p className="mt-4 text-base leading-relaxed text-[#8A93A6]">
                    {description}
                </p>
            )}
        </div>
    );
}
