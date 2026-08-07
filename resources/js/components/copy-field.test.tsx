import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, test, vi } from 'vitest';
import { CopyField } from '@/components/copy-field';

const copy = vi.fn().mockResolvedValue(true);

vi.mock('@/hooks/use-clipboard', () => ({
    useClipboard: () => [null, copy],
}));

describe('CopyField', () => {
    test('renders the value in a read-only input', () => {
        render(<CopyField value="secret-value-123" />);

        const input = screen.getByDisplayValue('secret-value-123');
        expect(input).toBeInTheDocument();
        expect(input).toHaveAttribute('readonly');
    });

    test('copies the value when the copy button is clicked', async () => {
        const user = userEvent.setup();
        render(<CopyField value="secret-value-123" />);

        await user.click(
            screen.getByRole('button', { name: /copy to clipboard/i }),
        );

        expect(copy).toHaveBeenCalledWith('secret-value-123');
    });
});
