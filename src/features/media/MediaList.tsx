import { FC, useMemo, useState } from "react";
import { MediaListItem, MediaListParams, MediaTypes, useListQuery } from "./mediaApi";
import { DataGrid, GridColDef, GridPaginationModel } from '@mui/x-data-grid';

const MediaList: FC = () => {
  const [paginationModel, setPaginationModel] = useState<GridPaginationModel>({
    page: 0,
    pageSize: 10
  })
  const params = useMemo<MediaListParams>(() => {
    return {
      limit: paginationModel.pageSize,
      offset: paginationModel.page * paginationModel.pageSize
    }
  }, [paginationModel]);
  const {
    data,
    // isError,
    // error,
    isLoading
  } = useListQuery(params);

  const columns: GridColDef<(typeof rows)[number]>[] = [
    { field: 'id', headerName: 'ID', width: 90 },
    {
      field: 'displayName',
      headerName: 'Name',
      width: 150,
      editable: true,
    },
    {
      field: 'url',
      headerName: 'Preview',
      width: 120,
      renderCell: (value) => {
        if (value.row.thumbnailUrl) {
          return (
            <img
              src={value.row.thumbnailUrl}
              alt="Preview"
              style={{ width: '100%', height: 'auto', maxHeight: '60px', objectFit: 'cover', borderRadius: '4px' }}
            />
          )
        }
        switch (value.row.mediaType) {
          case MediaTypes.image:
          case MediaTypes.video:
            break;
          default:
            return null;
        }
        return (
          <img
            src={value.row.url}
            alt="Preview"
            style={{ width: '100%', height: 'auto', maxHeight: '60px', objectFit: 'cover', borderRadius: '4px' }}
          />
        )
      }
    },
    {
      field: 'duration',
      headerName: 'Duration',
      type: 'number',
      width: 110,
      editable: true,
      valueGetter: (value: number) => msAsDuration(value)
    },
    {
      field: 'dimensions',
      headerName: 'Size',
      sortable: false,
      width: 160,
    },
    {
      field: 'mediaType',
      headerName: 'type',
      width: 150,
      editable: true
    },
    {
      field: 'hasAudio',
      headerName: 'Audio',
      width: 150,
      editable: true,
      valueGetter: (value: number, row: MediaListItem) => {
        if (row.mediaType !== MediaTypes.video) return;
        return value === 1 ? 'Sound' : 'Silent';
      }
    },
  ];
  const rows = data?.rows ?? [];

  return <DataGrid
    rows={rows}
    columns={columns}
    initialState={{
      pagination: {
        paginationModel
      },
    }}
    paginationMode="server"
    rowCount={data?.total ?? 0}
    onPaginationModelChange={setPaginationModel}
    loading={isLoading}
    pageSizeOptions={[5, 10]}
    checkboxSelection
    disableRowSelectionOnClick
  />;
}

const msAsDuration = (totalMs: number) => {
  if ((totalMs ?? 0) === 0) return;
  const ms = totalMs % 1000;
  totalMs -= ms;
  totalMs /= 1000;
  const s = totalMs % 60;
  totalMs -= s;
  totalMs /= 60;
  const min = totalMs % 60;
  totalMs -= min;
  totalMs /= 60;
  const hour = totalMs % 24;
  totalMs -= hour;
  totalMs /= 25;
  const day = totalMs;

  const parts = [day, hour, min, s];
  while (parts.length !== 2 && parts[0] === 0) {
    parts.shift();
  }
  let duration = parts.map(part => part.toString().padStart(2, '0')).join(':');
  if (ms > 0) duration += `.${ms.toString().padStart(3, '0')}`;
  return duration;

}
/*
const _ImageItem: FC<{
  item: MediaListItem
}> = ({ item: {
  width,
  height,
  displayName,
  url,
  createdAt,
  duration,
  videoFormat,
  audioFormat,
  fileFormat
} }) => {
    let created = new Date(createdAt * 1000);
    let iWidth = width ?? 164;
    let iHeight = height ?? 164;
    if (iWidth > 164) {
      iHeight *= 164 / iWidth;
      iWidth = 164;
    }
    if (iHeight > 164) {
      iWidth *= 164 / iHeight;
      iHeight = 164;
    }

    return <Grid size={3}>
      <Card>
        <CardHeader
          action={
            <IconButton aria-label="settings">
              <MoreVertIcon />
            </IconButton>
          }
          title={displayName}
          subheader={created.toLocaleDateString()}
        />
        <CardMedia
          component="img"
          height={iHeight}
          image={url}
          alt={displayName}
        />
        <CardContent>
          {videoFormat ?? audioFormat ?? fileFormat}
          <Typography variant="body2" sx={{ color: 'text.secondary' }}>
            {msAsDuration(duration)}
          </Typography>
        </CardContent>
        <CardActions disableSpacing>
          <IconButton aria-label="edit">
            <EditIcon />
          </IconButton>
          <IconButton aria-label="share">
            <DeleteIcon />
          </IconButton>

          <ExpandMoreIcon />
        </CardActions>
      </Card>
    </Grid>
  }
    */
export default MediaList;
