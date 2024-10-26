import { injectReducer } from "@/app/injectReducer";
import { createAppSlice } from "@/app/createAppSlice";
import emoji from '@lewismoten/emoji';
import "./mediaApi";

interface MediaState {

}
const initialState: MediaState = {};

export const mediaSlice = createAppSlice({
  name: `media ${emoji.framedPicture}`,
  reducerPath: `media ${emoji.framedPicture}`,
  initialState,
  reducers: create => ({
    foo: create.reducer((
      _state
    ) => {

    })
  })
});

export const {
  foo
} = mediaSlice.actions;

injectReducer(
  mediaSlice.reducerPath,
  mediaSlice.reducer
);