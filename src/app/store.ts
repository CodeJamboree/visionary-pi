import {
  configureStore,
  createDynamicMiddleware,
  combineReducers,
  type Reducer
} from "@reduxjs/toolkit";

export const dynamicReducers: Record<string, Reducer> = {};
export const dynamicMiddleware = createDynamicMiddleware();

export const createRootReducer = () =>
  combineReducers({
    root: (state: any = {}) => state,
    ...dynamicReducers
  });

const createAppStore = () => configureStore({
  reducer: createRootReducer(),
  middleware: getDefaultMiddleware => getDefaultMiddleware()
    .prepend(dynamicMiddleware.middleware)
});

export const store = createAppStore();
