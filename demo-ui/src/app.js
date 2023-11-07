const { algoliasearch, instantsearch } = window;
const { autocomplete } = window['@algolia/autocomplete-js'];
const { createLocalStorageRecentSearchesPlugin } = window[
  '@algolia/autocomplete-plugin-recent-searches'
];
const { createQuerySuggestionsPlugin } = window[
  '@algolia/autocomplete-plugin-query-suggestions'
];

const searchClient = algoliasearch('PJVO9AK6J5', '8cdf0500eb7c625a7225797cdf4faa68');

const search = instantsearch({
  indexName: 'algolia-assignment',
  searchClient,
  future: { preserveSharedStateOnUnmount: true },
  insights: true,
});

const virtualSearchBox = instantsearch.connectors.connectSearchBox(() => {});

search.addWidgets([
  virtualSearchBox({}),
  instantsearch.widgets.hits({
    container: '#hits',
    templates: {
      item: (hit, { html, components }) => html`
<article>
  <h1>${components.Highlight({hit, attribute: "name"})}</h1>
  <p>${components.Highlight({ hit, attribute: 'description' })}</p>
  <p>$ ${hit.price}</p>
</article>
`,
    },
  }),
  instantsearch.widgets.configure({
    hitsPerPage: 8,
  }),
  instantsearch.widgets.pagination({
    container: '#pagination',
  }),

  
]);


search.addWidgets([
  instantsearch.widgets.sortBy({
    container: '#sort-by',
    items: [
      { label: 'Featured', value: 'algolia-assignment' },
      { label: 'Popularity', value: 'algolia-assignment_popularity_desc' },
      { label: 'Price (Low to High)', value: 'algolia-assignment_price_asc' },
      { label: 'Price (High to Low)', value: 'algolia-assignment_price_desc' },
    ],
  }),

  instantsearch.widgets.clearRefinements({
    container: '#clear-refinements',
  }),

  instantsearch.widgets.refinementList({
    container: '#brand-list',
    attribute: 'brand',
  }),

  instantsearch.widgets.refinementList({
    container: '#categories-list',
    attribute: 'categories',
  }),

  instantsearch.widgets.toggleRefinement({
    container: '#free-shipping-list',
    attribute: 'free_shipping',
    templates: {
      labelText({ count }, { html }) {
        return html`Free shipping (${count.toLocaleString()})`;
      },
    },
  }),

  instantsearch.widgets.numericMenu({
    container: '#price-list',
    attribute: 'price',
    items: [
      { label: 'All' },
      { label: 'Less than $500', end: 500 },
      { label: 'Between $500 - $1000', start: 500, end: 1000 },
      { label: 'More than $1000', start: 1000 },
    ],
  }),

  instantsearch.widgets.ratingMenu({
    container: '#rating-list',
    attribute: 'rating',
  }),

  instantsearch.widgets.configure({
    hitsPerPage: 8
  }),
]);

search.start();

const recentSearchesPlugin = createLocalStorageRecentSearchesPlugin({
  key: 'instantsearch',
  limit: 3,
  transformSource({ source }) {
    return {
      ...source,
      onSelect({ setIsOpen, setQuery, item, event }) {
        onSelect({ setQuery, setIsOpen, event, query: item.label });
      },
    };
  },
});

const querySuggestionsPlugin = createQuerySuggestionsPlugin({
  searchClient,
  indexName: 'instant_search_demo_query_suggestions',
  getSearchParams() {
    return recentSearchesPlugin.data.getAlgoliaSearchParams({ hitsPerPage: 6 });
  },
  transformSource({ source }) {
    return {
      ...source,
      sourceId: 'querySuggestionsPlugin',
      onSelect({ setIsOpen, setQuery, event, item }) {
        onSelect({ setQuery, setIsOpen, event, query: item.query });
      },
      getItems(params) {
        if (!params.state.query) {
          return [];
        }

        return source.getItems(params);
      },
    };
  },
});

autocomplete({
  container: '#searchbox',
  openOnFocus: true,
  detachedMediaQuery: 'none',
  onSubmit({ state }) {
    setInstantSearchUiState({ query: state.query });
  },
  plugins: [recentSearchesPlugin, querySuggestionsPlugin],
});

function setInstantSearchUiState(indexUiState) {
  search.mainIndex.setIndexUiState({ page: 1, ...indexUiState });
}

function onSelect({ setIsOpen, setQuery, event, query }) {
  if (isModifierEvent(event)) {
    return;
  }

  setQuery(query);
  setIsOpen(false);
  setInstantSearchUiState({ query });
}

function isModifierEvent(event) {
  const isMiddleClick = event.button === 1;

  return (
    isMiddleClick ||
    event.altKey ||
    event.ctrlKey ||
    event.metaKey ||
    event.shiftKey
  );
}
