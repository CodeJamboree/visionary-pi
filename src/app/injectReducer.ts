import { Middleware, Reducer } from "@reduxjs/toolkit";
import { createRootReducer, dynamicMiddleware, dynamicReducers, store } from "./store";

const versions: Record<string, number> = {};

export const injectReducer = (
  key: string, reducer: Reducer, middleware?: Middleware) => {
  dynamicReducers[key] = reducer;
  store.replaceReducer(createRootReducer());
  if (!middleware) {
    if (key in versions) {
      // deactivate
      versions[key]++;
    }
    return;
  }

  const version = (versions[key] ?? 0) + 1;
  versions[key] = version;
  const isLatest = () => versions[key] === version;
  dynamicMiddleware.addMiddleware(
    conditionalMiddleware(middleware, isLatest)
  );
}

const conditionalMiddleware = (
  middleware: Middleware,
  meetsCondition: () => boolean
): Middleware =>
  api => {
    if (!meetsCondition()) {
      return next => action => next(action);
    }
    const mApi = middleware(api);
    return next => {
      if (!meetsCondition()) {
        return action => next(action);
      }
      const mNext = mApi(next);
      return action => meetsCondition() ?
        mNext(action) :
        next(action)
    }
  }
