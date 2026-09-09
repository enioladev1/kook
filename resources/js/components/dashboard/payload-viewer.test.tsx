import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test, vi } from 'vitest';
import { PayloadViewer } from '@/components/dashboard/payload-viewer';

const copy = vi.fn().mockResolvedValue(true);

vi.mock('@/hooks/use-clipboard', () => ({
    useClipboard: () => [null, copy],
}));

describe('PayloadViewer', () => {
    test('copies the raw body verbatim when the copy button is clicked', async () => {
        const raw = '{"z":1,"a":2}';
        const user = userEvent.setup();
        render(<PayloadViewer raw={raw} />);

        await user.click(screen.getByRole('button', { name: /copy payload/i }));

        expect(copy).toHaveBeenCalledWith(raw);
    });

    test('renders keys in their original order', () => {
        const { container } = render(<PayloadViewer raw='{"z":1,"a":2}' />);

        expect(container.querySelector('pre')?.textContent).toBe(
            '{\n  "z": 1,\n  "a": 2\n}',
        );
    });

    test('falls back to plain text for non-JSON bodies', () => {
        render(<PayloadViewer raw="plain text body" />);

        expect(screen.getByText('plain text body')).toBeInTheDocument();
    });
});
