import Router from '@/containers/Router';
import StateProvider from '@/components/StateProvider';
import { DelayedSuspense } from '@/components/DelayedSuspense';
import { store } from './store';
import './index.scss'

const App = () => (
  <StateProvider store={store}>
    <DelayedSuspense fallback="Loading Router">
      <Router />
    </DelayedSuspense>
  </StateProvider>
)
export default App;