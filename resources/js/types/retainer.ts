export type Retainer = {
    id: number;
    name: string;
    data_center: string;
    server: string;
};

export type RetainerListingsSummary = {
    retainer_id: number;
    retainer_name: string;
    server: string;
    items: RetainerListingsSummaryItem[];
};

export type RetainerListingsSummaryItem = {
    item_id: number;
    item_name: string;
    retainer_listing_price: number | null;
    num_retainer_listings: number;
    lowest_listing_price: number | null;
};