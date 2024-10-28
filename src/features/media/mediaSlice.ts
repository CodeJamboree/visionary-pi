import { injectReducer } from "@/app/injectReducer";
import { createAppSlice } from "@/app/createAppSlice";
import emoji from '@lewismoten/emoji';
import "./mediaApi";
import { PayloadAction } from "@reduxjs/toolkit";

interface MediaState {
  selectedIds: number[]
}
const initialState: MediaState = {
  selectedIds: []
};

export const mediaSlice = createAppSlice({
  name: `media ${emoji.framedPicture}`,
  reducerPath: `media ${emoji.framedPicture}`,
  initialState,
  reducers: create => ({
    selectionChanged: create.reducer((
      state,
      action: PayloadAction<{ selectedIds: number[] }>
    ) => {
      state.selectedIds = action.payload.selectedIds
    }),
    clearSelection: create.reducer((
      state
    ) => {
      state.selectedIds = []
    })
  }),
  selectors: {
    selectSelectedIds: ({ selectedIds }) => selectedIds,
  }
});

export const {
  selectionChanged,
  clearSelection
} = mediaSlice.actions;

export const {
  selectSelectedIds
} = mediaSlice.selectors;

injectReducer(
  mediaSlice.reducerPath,
  mediaSlice.reducer
);