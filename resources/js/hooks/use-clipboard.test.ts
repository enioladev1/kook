import { act, renderHook, waitFor } from '@testing-library/react';
import { describe, expect, test, vi } from 'vitest';
import { useClipboard } from '@/hooks/use-clipboard';

describe('useClipboard', () => {
    test('copies text and tracks the most recently copied value', async () => {
        Object.defineProperty(navigator, 'clipboard', {
            value: { writeText: vi.fn().mockResolvedValue(undefined) },
            configurable: true,
            writable: true,
        });

        const { result } = renderHook(() => useClipboard());
        expect(result.current[0]).toBeNull();

        await act(async () => {
            await result.current[1]('hello world');
        });

        expect(navigator.clipboard.writeText).toHaveBeenCalledWith(
            'hello world',
        );
        await waitFor(() => expect(result.current[0]).toBe('hello world'));
    });

    test('returns false and leaves state unset when the clipboard API is unavailable', async () => {
        Object.defineProperty(navigator, 'clipboard', {
            value: undefined,
            configurable: true,
            writable: true,
        });

        const { result } = renderHook(() => useClipboard());

        let copied: boolean | undefined;
        await act(async () => {
            copied = await result.current[1]('hello world');
        });

        expect(copied).toBe(false);
        expect(result.current[0]).toBeNull();
    });
});
