import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import ErrorBoundary from '@/components/ErrorBoundary';
import { DelayedSuspense } from '@/components/DelayedSuspense';
import { ConnectionQualityProvider } from '@/components/ConnectionQualityProvider';
import App from '@/app';

const root = document.getElementById('root');

if (root === null) {

  console.error('Root element not found');

} else {

  if (root.hasAttribute('data-timeout')) {
    const timeoutId = root.getAttribute('data-timeout')!;
    clearTimeout(timeoutId);
    root.removeAttribute('data-timeout');
  }
  let initialized = new Date();
  if (root.hasAttribute('data-ts')) {
    initialized = new Date(parseInt(root.getAttribute('data-ts')!, 10));
  }

  createRoot(root)
    .render(
      <StrictMode>
        <ConnectionQualityProvider initialized={initialized}>
          <DelayedSuspense fallback="Loading Error Handler">
            <ErrorBoundary fallback="Something went wrong">
              <DelayedSuspense fallback="Loading App">
                <App />
              </DelayedSuspense>
            </ErrorBoundary>
          </DelayedSuspense>
        </ConnectionQualityProvider>
      </StrictMode >
    );
}