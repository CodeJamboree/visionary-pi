import { ReactElement } from "react";
import { Provider } from "react-redux";

const StateProvider: React.FC<{
  store: any;
  children: ReactElement;
}> = ({ store, children }) => (
  <Provider store={store}>{children}</Provider>
);

export default StateProvider;