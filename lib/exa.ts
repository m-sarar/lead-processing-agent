import Exa from 'exa-js';

const hasExaKey = process.env.EXA_API_KEY;

if (!hasExaKey) {
  console.warn(
    '⚠️  EXA_API_KEY is not set. Exa search functionality will be disabled.'
  );
}

// Lazy-initialize Exa client only if API key is available
export const exa = hasExaKey ? new Exa(process.env.EXA_API_KEY) : null;
