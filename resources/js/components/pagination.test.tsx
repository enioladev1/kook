import { render, screen } from '@testing-library/react';
import { describe, expect, test } from 'vitest';
import { Pagination } from '@/components/pagination';
import type { Paginated } from '@/types';

function paginatorOf(
    overrides: Partial<Paginated<unknown>> = {},
): Paginated<unknown> {
    return {
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 25,
        total: 0,
        links: [
            { url: null, label: '&laquo; Previous', active: false },
            { url: '/audit-logs?page=1', label: '1', active: true },
            { url: '/audit-logs?page=2', label: '2', active: false },
            { url: '/audit-logs?page=2', label: 'Next &raquo;', active: false },
        ],
        ...overrides,
    };
}

describe('Pagination', () => {
    test('renders nothing when there is only one page', () => {
        const { container } = render(
            <Pagination paginator={paginatorOf({ last_page: 1 })} />,
        );

        expect(container).toBeEmptyDOMElement();
    });

    test('renders a link for every page when there is more than one page', () => {
        render(<Pagination paginator={paginatorOf({ last_page: 2 })} />);

        expect(screen.getByText('1')).toBeInTheDocument();
        expect(screen.getByText('2')).toBeInTheDocument();
    });

    test('disables links with no url', () => {
        render(<Pagination paginator={paginatorOf({ last_page: 2 })} />);

        const previousLink = screen.getByText('« Previous');
        expect(previousLink).toHaveClass('pointer-events-none');
    });
});
